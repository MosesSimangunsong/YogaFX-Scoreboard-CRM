<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ParticipantAccessLink;
use App\Models\ScoreboardQuestion;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use App\Services\Delivery\QueueSubmissionResultDelivery;
use App\Services\Scoring\ScoreSubmission;
use App\Services\Scoreboards\ScoreboardFlow;
use App\Support\Scoreboards\SubmissionMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ScoreboardAssessmentController extends Controller
{
    public function show(
        Request $request,
        string $accessCode,
        ScoreboardFlow $flow,
    ): Response|RedirectResponse {
        $accessLink = $this->resolveAccessLink($accessCode);
        $submission = $this->resolveDraftSubmission($accessLink);

        if ($submission->status === 'submitted') {
            return redirect()->route('public.scoreboards.completed', $accessLink->access_code);
        }

        $questions = $this->orderedQuestions($accessLink);
        abort_if($questions->isEmpty(), 404);

        $currentQuestion = $this->resolveCurrentQuestion(
            $questions,
            $submission,
            $request->string('question')->toString(),
            $accessLink->scoreboard->allow_back_navigation,
        );

        $activePath = $flow->activePath($questions, $submission);
        $activePathIndex = $activePath->search(fn ($question) => $question->id === $currentQuestion->id);
        $previousQuestion = $activePathIndex > 0 ? $activePath[$activePathIndex - 1] : null;
        $existingAnswer = $submission->answers->firstWhere('scoreboard_question_id', $currentQuestion->id);
        $nextQuestionId = $flow->nextQuestionId($questions, $currentQuestion, $existingAnswer);

        return Inertia::render('Public/Scoreboards/Assessment', [
            'accessLink' => [
                'code' => $accessLink->access_code,
            ],
            'scoreboard' => [
                'title' => $accessLink->scoreboard->title,
                'slug' => $accessLink->scoreboard->slug,
                'description' => $accessLink->scoreboard->description,
                'show_progress_bar' => $accessLink->scoreboard->show_progress_bar,
                'allow_back_navigation' => $accessLink->scoreboard->allow_back_navigation,
                'duration_minutes' => $accessLink->scoreboard->duration_minutes,
            ],
            'participant' => [
                'first_name' => $accessLink->participant->first_name,
                'last_name' => $accessLink->participant->last_name,
            ],
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'started_at' => optional($submission->started_at)->toIso8601String(),
            ],
            'progress' => [
                'current_step' => max(1, ($activePathIndex === false ? $activePath->count() : $activePathIndex + 1)),
                'total_steps' => $questions->count(),
                'completed_steps' => collect(data_get($submission->progress_payload, 'visited_question_ids', []))->unique()->count(),
            ],
            'question' => $this->serializeQuestion($currentQuestion, $submission, $flow),
            'answer' => [
                'selected_option_ids' => $existingAnswer?->selected_option_ids ?? [],
                'answer_text' => $existingAnswer?->answer_text ?? '',
                'answer_number' => $existingAnswer?->answer_number,
                'other_text' => data_get($existingAnswer?->answer_payload, 'other_text', ''),
            ],
            'navigation' => [
                'previous_url' => $previousQuestion && $accessLink->scoreboard->allow_back_navigation
                    ? route('public.scoreboards.assessment.show', [
                        'accessCode' => $accessLink->access_code,
                        'question' => $previousQuestion->id,
                    ])
                    : null,
                'is_last_question' => $nextQuestionId === null,
            ],
            'status' => session('status'),
        ]);
    }

    public function store(
        Request $request,
        string $accessCode,
        ScoreSubmission $scoreSubmission,
        QueueSubmissionResultDelivery $queueSubmissionResultDelivery,
        ScoreboardFlow $flow,
    ): RedirectResponse {
        $accessLink = $this->resolveAccessLink($accessCode);
        $submission = $this->resolveDraftSubmission($accessLink);

        if ($submission->status === 'submitted') {
            return redirect()->route('public.scoreboards.completed', $accessLink->access_code);
        }

        $questions = $this->orderedQuestions($accessLink);
        abort_if($questions->isEmpty(), 404);

        $question = $questions->firstWhere('id', (int) $request->integer('question_id'));
        abort_unless($question, 404);

        $validated = $this->validateAnswer($request, $question);
        $answer = $this->upsertAnswer($submission, $question, $validated);
        $nextQuestionId = $flow->nextQuestionId($questions, $question, $answer);
        $visitedQuestionIds = $flow->appendVisitedQuestion($submission, $question->id);

        if (! $nextQuestionId) {
            $submission->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'completed_at' => now(),
                'current_question_id' => null,
                'last_answered_question_id' => $question->id,
                'finished_reason' => 'completed',
                'progress_payload' => [
                    ...($submission->progress_payload ?? []),
                    'visited_question_ids' => $visitedQuestionIds,
                ],
            ]);

            $submission = $scoreSubmission->execute($submission);
            $queueSubmissionResultDelivery->execute($submission);

            return redirect()
                ->route('public.scoreboards.completed', $accessLink->access_code)
                ->with('status', 'assessment-delivery-queued');
        }

        $submission->update([
            'status' => 'in_progress',
            'current_question_id' => $nextQuestionId,
            'last_answered_question_id' => $question->id,
            'progress_payload' => [
                ...($submission->progress_payload ?? []),
                'visited_question_ids' => $visitedQuestionIds,
            ],
        ]);

        return redirect()->route('public.scoreboards.assessment.show', [
            'accessCode' => $accessLink->access_code,
            'question' => $nextQuestionId,
        ]);
    }

    public function completed(string $accessCode): Response|RedirectResponse
    {
        $accessLink = $this->resolveAccessLink($accessCode);

        $submission = $accessLink->submissions()
            ->withCount('answers')
            ->with([
                'categoryScores',
                'resultRange',
                'pdfReports' => fn ($query) => $query->latest('id'),
                'emailLogs' => fn ($query) => $query->latest('id'),
            ])
            ->latest('submitted_at')
            ->latest('id')
            ->first();

        if (! $submission || $submission->status !== 'submitted') {
            return redirect()->route('public.scoreboards.assessment.show', $accessLink->access_code);
        }

        return Inertia::render('Public/Scoreboards/Result', [
            'accessLink' => [
                'code' => $accessLink->access_code,
            ],
            'scoreboard' => [
                'title' => $accessLink->scoreboard->title,
                'slug' => $accessLink->scoreboard->slug,
                'result_mode' => $accessLink->scoreboard->result_mode,
            ],
            'participant' => [
                'first_name' => $accessLink->participant->first_name,
                'last_name' => $accessLink->participant->last_name,
            ],
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'answers_count' => $submission->answers_count,
                'submitted_at' => optional($submission->submitted_at)->toIso8601String(),
                'overall_score' => $submission->overall_score,
                'scored_at' => optional($submission->scored_at)->toIso8601String(),
                'correct_answers_count' => data_get($submission->score_payload, 'correct_answers_count'),
                'gradable_questions_count' => data_get($submission->score_payload, 'gradable_questions_count'),
                'percentage' => data_get($submission->score_payload, 'percentage'),
                'time_taken' => SubmissionMetrics::resolveTimeTaken($submission),
            ],
            'result' => [
                'title' => $submission->result_title,
                'description' => $submission->result_description,
                'recommendation' => $submission->result_recommendation,
                'range_id' => $submission->scoreboard_result_range_id,
            ],
            'categoryScores' => $submission->categoryScores
                ->sortByDesc('score')
                ->values()
                ->map(fn ($item) => [
                    'category_key' => $item->category_key,
                    'score' => $item->score,
                    'answered_questions_count' => $item->answered_questions_count,
                ]),
            'display' => [
                'show_score' => in_array($accessLink->scoreboard->result_mode, ['score_only', 'score_or_range'], true),
                'show_range' => in_array($accessLink->scoreboard->result_mode, ['range_only', 'score_or_range'], true),
            ],
            'delivery' => [
                'pdf_report' => $submission->pdfReports->first()
                    ? [
                        'status' => $submission->pdfReports->first()->status,
                        'file_name' => $submission->pdfReports->first()->file_name,
                        'generated_at' => optional($submission->pdfReports->first()->generated_at)->toIso8601String(),
                    ]
                    : null,
                'email_log' => $submission->emailLogs->first()
                    ? [
                        'status' => $submission->emailLogs->first()->status,
                        'recipient_email' => $submission->emailLogs->first()->recipient_email,
                        'queued_at' => optional($submission->emailLogs->first()->queued_at)->toIso8601String(),
                        'sent_at' => optional($submission->emailLogs->first()->sent_at)->toIso8601String(),
                        'error_message' => $submission->emailLogs->first()->error_message,
                    ]
                    : null,
            ],
            'status' => session('status'),
        ]);
    }

    private function resolveAccessLink(string $accessCode): ParticipantAccessLink
    {
        return ParticipantAccessLink::query()
            ->with([
                'participant',
                'scoreboard',
                'submissions.answers',
                'scoreboard.questions.options',
            ])
            ->where('access_code', $accessCode)
            ->resolvable()
            ->firstOrFail();
    }

    private function orderedQuestions(ParticipantAccessLink $accessLink)
    {
        return $accessLink->scoreboard->questions
            ->sortBy('sort_order')
            ->values();
    }

    private function resolveDraftSubmission(ParticipantAccessLink $accessLink): Submission
    {
        $existing = $accessLink->submissions
            ->sortByDesc(fn ($submission) => $submission->submitted_at ?? $submission->created_at)
            ->first();

        if ($existing) {
            return $existing;
        }

        $firstQuestionId = $accessLink->scoreboard->questions
            ->sortBy('sort_order')
            ->first()?->id;

        return Submission::create([
            'scoreboard_id' => $accessLink->scoreboard_id,
            'participant_id' => $accessLink->participant_id,
            'participant_access_link_id' => $accessLink->id,
            'status' => 'in_progress',
            'started_at' => now(),
            'current_question_id' => $firstQuestionId,
            'tracking_payload' => $accessLink->participant->tracking_payload,
            'progress_payload' => [
                'visited_question_ids' => [],
            ],
        ])->load('answers');
    }

    private function resolveCurrentQuestion(
        $questions,
        Submission $submission,
        string $requestedQuestionId,
        bool $allowBackNavigation,
    ): ScoreboardQuestion
    {
        $activePathIds = collect(data_get($submission->progress_payload, 'visited_question_ids', []))
            ->push($submission->current_question_id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        if ($allowBackNavigation && $requestedQuestionId !== '') {
            $requested = $questions->firstWhere('id', (int) $requestedQuestionId);

            if ($requested && $activePathIds->contains($requested->id)) {
                return $requested;
            }
        }

        if ($submission->current_question_id) {
            $current = $questions->firstWhere('id', $submission->current_question_id);

            if ($current) {
                return $current;
            }
        }

        return $questions->first();
    }

    private function validateAnswer(Request $request, ScoreboardQuestion $question): array
    {
        $optionIds = $question->options->pluck('id')->all();
        $singleSelectTypes = ['yes_no_maybe', 'radio_buttons'];
        $multiSelectTypes = ['multiple_choice_buttons', 'multiple_choice_checkboxes', 'image_button'];
        $scaleTypes = ['sliding_scale', 'linear_scale', 'divided_scale'];
        $rules = [
            'question_id' => ['required', 'integer'],
            'selected_option_ids' => ['nullable', 'array'],
            'selected_option_ids.*' => [
                'integer',
                Rule::exists('scoreboard_question_options', 'id')->where(
                    fn ($query) => $query->where('question_id', $question->id),
                ),
            ],
            'answer_text' => ['nullable', 'string'],
            'answer_number' => ['nullable', 'numeric'],
            'other_text' => ['nullable', 'string'],
        ];

        if (in_array($question->question_type, $singleSelectTypes, true)) {
            $rules['selected_option_ids'][] = $question->required ? 'required' : 'nullable';
            $rules['selected_option_ids'][] = 'size:1';
        }

        if (in_array($question->question_type, $multiSelectTypes, true)) {
            if ($question->required) {
                $rules['selected_option_ids'][] = 'required';
                $rules['selected_option_ids'][] = 'min:1';
            }

            if (! $question->allow_multi_select) {
                $rules['selected_option_ids'][] = 'max:1';
            }

            if ($question->min_count) {
                $rules['selected_option_ids'][] = 'min:'.$question->min_count;
            }

            if ($question->max_count) {
                $rules['selected_option_ids'][] = 'max:'.$question->max_count;
            }
        }

        if (in_array($question->question_type, ['numeric', ...$scaleTypes], true)) {
            $rules['answer_number'][] = $question->required ? 'required' : 'nullable';

            if (! $question->allow_decimals) {
                $rules['answer_number'][] = 'integer';
            }

            if ($question->score_range_min !== null) {
                $rules['answer_number'][] = 'gte:'.$question->score_range_min;
            }

            if ($question->score_range_max !== null) {
                $rules['answer_number'][] = 'lte:'.$question->score_range_max;
            }
        }

        if ($question->question_type === 'open_text') {
            $rules['answer_text'][] = $question->required ? 'required' : 'nullable';

            if ($question->character_limit) {
                $rules['answer_text'][] = 'max:'.$question->character_limit;
            }
        }

        if ($question->question_type === 'info_screen') {
            $rules['selected_option_ids'] = ['nullable', 'array'];
            $rules['answer_text'] = ['nullable', 'string'];
            $rules['answer_number'] = ['nullable', 'numeric'];
        }

        $validated = $request->validate($rules);

        $selectedOptionIds = collect($validated['selected_option_ids'] ?? [])->map(fn ($id) => (int) $id)->values();
        $selectedOtherOption = $question->options
            ->where('is_other_option', true)
            ->first(fn ($option) => $selectedOptionIds->contains($option->id));

        if ($selectedOtherOption && blank($validated['other_text'] ?? null)) {
            $request->validate([
                'other_text' => ['required', 'string', 'max:1000'],
            ]);
        }

        if ($optionIds === [] && in_array($question->question_type, [...$singleSelectTypes, ...$multiSelectTypes], true)) {
            abort(422, 'Question options are not configured for this scoreboard question.');
        }

        return $validated;
    }

    private function upsertAnswer(Submission $submission, ScoreboardQuestion $question, array $validated): ?SubmissionAnswer
    {
        if ($question->question_type === 'info_screen') {
            $submission->update([
                'status' => 'in_progress',
                'last_answered_at' => now(),
            ]);

            return null;
        }

        $selectedOptionIds = collect($validated['selected_option_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $selectedOtherOption = $question->options
            ->first(fn ($option) => $option->is_other_option && in_array($option->id, $selectedOptionIds, true));

        $selectedPrimaryOptionId = count($selectedOptionIds) === 1 ? $selectedOptionIds[0] : null;
        $selectedOptions = $question->options
            ->whereIn('id', $selectedOptionIds)
            ->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
                'internal_value' => $option->internal_value,
                'score_value' => $option->score_value,
                'is_other_option' => $option->is_other_option,
            ])
            ->values()
            ->all();

        $answer = SubmissionAnswer::query()->updateOrCreate(
            [
                'submission_id' => $submission->id,
                'scoreboard_question_id' => $question->id,
            ],
            [
                'scoreboard_question_option_id' => $selectedPrimaryOptionId,
                'selected_option_ids' => $selectedOptionIds,
                'answer_text' => $selectedOtherOption
                    ? ($validated['other_text'] ?? null)
                    : ($validated['answer_text'] ?? null),
                'answer_number' => $validated['answer_number'] ?? null,
                'answer_payload' => [
                    'question_type' => $question->question_type,
                    'selected_options' => $selectedOptions,
                    'other_text' => $validated['other_text'] ?? null,
                ],
                'question_snapshot' => [
                    'id' => $question->id,
                    'title' => $question->title,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'required' => $question->required,
                    'sort_order' => $question->sort_order,
                    'scoring_category' => $question->scoring_category,
                ],
                'answered_at' => now(),
            ],
        );

        $submission->update([
            'status' => 'in_progress',
            'last_answered_at' => now(),
        ]);

        return $answer;
    }

    private function serializeQuestion(
        ScoreboardQuestion $question,
        Submission $submission,
        ScoreboardFlow $flow,
    ): array {
        return [
            'id' => $question->id,
            'title' => $question->title,
            'question_text' => $question->question_text,
            'question_type' => $question->question_type,
            'instruction_text' => $question->show_instruction ? $question->instruction_text : null,
            'required' => $question->required,
            'allow_multi_select' => $question->allow_multi_select,
            'min_count' => $question->min_count,
            'max_count' => $question->max_count,
            'character_limit' => $question->character_limit,
            'score_range_min' => $question->score_range_min,
            'score_range_max' => $question->score_range_max,
            'starting_score' => $question->starting_score,
            'section_count' => $question->section_count,
            'allow_decimals' => $question->allow_decimals,
            'left_label' => $question->left_label,
            'center_label' => $question->center_label,
            'right_label' => $question->right_label,
            'options' => $flow->stableOptions($question, $submission)
                ->filter(fn ($option) => $question->question_type !== 'yes_no_maybe' || $question->show_maybe_answer || $option->internal_value !== 'maybe')
                ->values()
                ->map(fn ($option) => [
                    'id' => $option->id,
                    'label' => $option->label,
                    'internal_value' => $option->internal_value,
                    'image_url' => $option->image_url,
                    'is_other_option' => $option->is_other_option,
                ]),
        ];
    }
}
