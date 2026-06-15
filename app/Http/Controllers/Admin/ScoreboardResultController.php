<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use App\Models\Submission;
use App\Support\Scoreboards\SubmissionMetrics;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ScoreboardResultController extends Controller
{
    public function index(Scoreboard $assessment): Response
    {
        $assessment->load(['submissions.participant']);

        $results = $assessment->submissions()
            ->with(['participant'])
            ->where('status', 'submitted')
            ->latest('submitted_at')
            ->latest('id')
            ->get()
            ->map(fn (Submission $submission) => [
                'id' => $submission->id,
                'name' => trim(($submission->participant?->first_name ?? '').' '.($submission->participant?->last_name ?? '')),
                'email' => $submission->participant?->email,
                'points' => $submission->overall_score,
                'correct_answers_label' => data_get($submission->score_payload, 'gradable_questions_count') > 0
                    ? sprintf(
                        '%s / %s',
                        data_get($submission->score_payload, 'correct_answers_count'),
                        data_get($submission->score_payload, 'gradable_questions_count'),
                    )
                    : null,
                'percentage' => data_get($submission->score_payload, 'percentage'),
                'completed_at' => optional($submission->submitted_at ?? $submission->completed_at)->diffForHumans(),
            ]);

        return Inertia::render('Admin/Scoreboards/ResultsIndex', [
            'scoreboard' => [
                'id' => $assessment->id,
                'title' => $assessment->title,
            ],
            'results' => $results,
            'status' => session('status'),
        ]);
    }

    public function show(Scoreboard $assessment, Submission $submission): Response
    {
        abort_unless($submission->scoreboard_id === $assessment->id, 404);

        $submission->load([
            'participant',
            'answers.question.options',
            'answers.selectedOption',
            'resultRange',
        ]);

        return Inertia::render('Admin/Scoreboards/ResultShow', [
            'scoreboard' => [
                'id' => $assessment->id,
                'title' => $assessment->title,
            ],
            'result' => [
                'id' => $submission->id,
                'name' => trim(($submission->participant?->first_name ?? '').' '.($submission->participant?->last_name ?? '')),
                'email' => $submission->participant?->email,
                'phone' => $submission->participant?->whatsapp,
                'points' => $submission->overall_score,
                'correct_answers_count' => data_get($submission->score_payload, 'correct_answers_count'),
                'gradable_questions_count' => data_get($submission->score_payload, 'gradable_questions_count'),
                'percentage' => data_get($submission->score_payload, 'percentage'),
                'time_taken' => SubmissionMetrics::resolveTimeTaken($submission),
                'submitted_at' => optional($submission->submitted_at)->toIso8601String(),
                'result_title' => $submission->result_title,
                'result_description' => $submission->result_description,
            ],
            'answers' => $submission->answers
                ->sortBy(fn ($answer) => data_get($answer->question_snapshot, 'sort_order', PHP_INT_MAX))
                ->values()
                ->map(function ($answer) {
                    $question = $answer->question;
                    $isGradable = $question ? SubmissionMetrics::questionIsGradable($question) : false;

                    return [
                        'id' => $answer->id,
                        'question_title' => data_get($answer->question_snapshot, 'title')
                            ?: $answer->question?->title
                            ?: 'Untitled Question',
                        'question_text' => data_get($answer->question_snapshot, 'question_text')
                            ?: $answer->question?->question_text,
                        'question_type' => data_get($answer->question_snapshot, 'question_type')
                            ?: $answer->question?->question_type,
                        'selected_options' => collect(data_get($answer->answer_payload, 'selected_options', []))
                            ->map(fn ($option) => [
                                'label' => $option['label'] ?? $option['internal_value'] ?? 'Option',
                                'score_value' => $option['score_value'] ?? null,
                                'is_other_option' => $option['is_other_option'] ?? false,
                            ])
                            ->values(),
                        'other_text' => data_get($answer->answer_payload, 'other_text'),
                        'answer_text' => $answer->answer_text,
                        'answer_number' => $answer->answer_number,
                        'is_gradable' => $isGradable,
                        'is_correct' => $question ? SubmissionMetrics::answerIsCorrect($question, $answer) : false,
                    ];
                }),
        ]);
    }

    public function destroy(Scoreboard $assessment, Submission $submission): RedirectResponse
    {
        abort_unless($submission->scoreboard_id === $assessment->id, 404);

        $submission->delete();

        return redirect()
            ->route('admin.scoreboards.results.index', $assessment)
            ->with('status', 'scoreboard-result-deleted');
    }
}
