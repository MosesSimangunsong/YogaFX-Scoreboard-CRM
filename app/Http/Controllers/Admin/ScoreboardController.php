<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Services\Scoreboards\ValidateScoreboardPublishReadiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ScoreboardController extends Controller
{
    public function index(): Response
    {
        $scoreboards = Scoreboard::query()
            ->withCount('questions')
            ->latest('updated_at')
            ->get()
            ->map(fn (Scoreboard $scoreboard) => [
                'id' => $scoreboard->id,
                'title' => $scoreboard->title,
                'status' => $scoreboard->status,
                'is_active' => $scoreboard->is_active,
                'questions_count' => $scoreboard->questions_count,
                'thumbnail_url' => $scoreboard->thumbnail_url,
                'updated_at' => optional($scoreboard->updated_at)->diffForHumans(),
            ]);

        return Inertia::render('Admin/Scoreboards/Index', [
            'scoreboards' => $scoreboards,
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Scoreboards/Create', $this->metaFormPayload());
    }

    public function store(
        Request $request,
        ValidateScoreboardPublishReadiness $validateScoreboardPublishReadiness,
    ): RedirectResponse
    {
        $validated = $this->validateMeta($request);
        $validateScoreboardPublishReadiness->execute(null, $validated);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail_path'] = $request->file('thumbnail')->store('scoreboards/thumbnails', 'public');
        }

        unset($validated['thumbnail']);
        $validated['created_by_user_id'] = $request->user()?->id;

        $scoreboard = Scoreboard::create($validated);

        return redirect()
            ->route('admin.scoreboards.builder', $scoreboard)
            ->with('status', 'scoreboard-created');
    }

    public function edit(Scoreboard $scoreboard): Response
    {
        return Inertia::render('Admin/Scoreboards/Edit', array_merge(
            $this->metaFormPayload(),
            [
                'scoreboard' => $this->serializeScoreboardMeta($scoreboard),
                'status' => session('status'),
            ],
        ));
    }

    public function update(
        Request $request,
        Scoreboard $scoreboard,
        ValidateScoreboardPublishReadiness $validateScoreboardPublishReadiness,
    ): RedirectResponse
    {
        $validated = $this->validateMeta($request, $scoreboard);
        $validateScoreboardPublishReadiness->execute($scoreboard, $validated);

        if ($request->hasFile('thumbnail')) {
            if ($scoreboard->thumbnail_path) {
                Storage::disk('public')->delete($scoreboard->thumbnail_path);
            }

            $validated['thumbnail_path'] = $request->file('thumbnail')->store('scoreboards/thumbnails', 'public');
        }

        unset($validated['thumbnail']);

        $scoreboard->update($validated);

        return redirect()
            ->route('admin.scoreboards.edit', $scoreboard)
            ->with('status', 'scoreboard-updated');
    }

    public function destroy(Scoreboard $scoreboard): RedirectResponse
    {
        if ($scoreboard->thumbnail_path) {
            Storage::disk('public')->delete($scoreboard->thumbnail_path);
        }

        if ($scoreboard->logo_path) {
            Storage::disk('public')->delete($scoreboard->logo_path);
        }

        $scoreboard->delete();

        return redirect()
            ->route('admin.scoreboards.index')
            ->with('status', 'scoreboard-deleted');
    }

    public function builder(Scoreboard $assessment, ?ScoreboardQuestion $question = null): Response
    {
        if ($question && $question->scoreboard_id !== $assessment->id) {
            abort(404);
        }

        $assessment->load([
            'questions.options',
            'resultRanges',
        ]);

        $selectedQuestionId = $question?->id ?? $assessment->questions->first()?->id;

        return Inertia::render('Admin/Scoreboards/Builder', [
            'scoreboard' => array_merge(
                $this->serializeScoreboardMeta($assessment),
                ['id' => $assessment->id],
            ),
            'questions' => $assessment->questions
                ->sortBy('sort_order')
                ->values()
                ->map(fn (ScoreboardQuestion $item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'question_text' => $item->question_text,
                    'question_type' => $item->question_type,
                    'sort_order' => $item->sort_order,
                    'show_instruction' => $item->show_instruction,
                    'instruction_text' => $item->instruction_text,
                    'required' => $item->required,
                    'randomize_answers_order' => $item->randomize_answers_order,
                    'jump_enabled' => $item->jump_enabled,
                    'jump_to_question_id' => $item->jump_to_question_id,
                    'show_maybe_answer' => $item->show_maybe_answer,
                    'allow_multi_select' => $item->allow_multi_select,
                    'min_count' => $item->min_count,
                    'max_count' => $item->max_count,
                    'allow_other_option' => $item->allow_other_option,
                    'show_labels' => $item->show_labels,
                    'score_range_min' => $item->score_range_min,
                    'score_range_max' => $item->score_range_max,
                    'starting_score' => $item->starting_score,
                    'section_count' => $item->section_count,
                    'allow_decimals' => $item->allow_decimals,
                    'input_type' => $item->input_type,
                    'character_limit' => $item->character_limit,
                    'show_score_tooltip' => $item->show_score_tooltip,
                    'score_tooltip_format' => $item->score_tooltip_format,
                    'answer_image_fit' => $item->answer_image_fit,
                    'answers_per_row' => $item->answers_per_row,
                    'scoring_category' => $item->scoring_category,
                    'left_label' => $item->left_label,
                    'center_label' => $item->center_label,
                    'right_label' => $item->right_label,
                    'options' => $item->options
                        ->sortBy('sort_order')
                        ->values()
                        ->map(fn ($option) => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'internal_value' => $option->internal_value,
                            'sort_order' => $option->sort_order,
                            'is_correct' => $option->is_correct,
                            'scoring_enabled' => $option->scoring_enabled,
                            'score_value' => $option->score_value,
                            'jump_enabled' => $option->jump_enabled,
                            'jump_to_question_id' => $option->jump_to_question_id,
                            'is_other_option' => $option->is_other_option,
                            'is_fixed_option' => $option->is_fixed_option,
                            'image_url' => $option->image_url,
                        ]),
                ]),
            'selectedQuestionId' => $selectedQuestionId,
            'design' => $assessment->toDesignPayload(),
            'resultRanges' => $assessment->resultRanges
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($range) => [
                    'id' => $range->id,
                    'title' => $range->title,
                    'description' => $range->description,
                    'min_score' => $range->min_score,
                    'max_score' => $range->max_score,
                    'sort_order' => $range->sort_order,
                ]),
            'questionTypeOptions' => $this->questionTypeOptions(),
            'questionTargets' => $assessment->questions
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'label' => trim(($item->title ?: 'Question '.$item->sort_order).' (#'.$item->sort_order.')'),
                ]),
            'status' => session('status'),
        ]);
    }

    private function metaFormPayload(): array
    {
        return [
            'statusOptions' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'published', 'label' => 'Published'],
                ['value' => 'archived', 'label' => 'Archived'],
            ],
            'scoringModeOptions' => [
                ['value' => 'points', 'label' => 'Points'],
                ['value' => 'weighted_points', 'label' => 'Weighted Points'],
                ['value' => 'raw_score', 'label' => 'Raw Score'],
            ],
            'resultModeOptions' => [
                ['value' => 'score_or_range', 'label' => 'Score or Range'],
                ['value' => 'score_only', 'label' => 'Score Only'],
                ['value' => 'range_only', 'label' => 'Range Only'],
            ],
        ];
    }

    private function validateMeta(Request $request, ?Scoreboard $scoreboard = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('scoreboards', 'slug')->ignore($scoreboard?->id),
            ],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'scoring_mode' => ['required', 'string', 'max:255'],
            'result_mode' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'show_progress_bar' => ['required', 'boolean'],
            'allow_back_navigation' => ['required', 'boolean'],
        ]);
    }

    private function serializeScoreboardMeta(Scoreboard $scoreboard): array
    {
        return [
            'id' => $scoreboard->id,
            'title' => $scoreboard->title,
            'slug' => $scoreboard->slug,
            'description' => $scoreboard->description,
            'thumbnail_url' => $scoreboard->thumbnail_url,
            'status' => $scoreboard->status,
            'duration_minutes' => $scoreboard->duration_minutes,
            'scoring_mode' => $scoreboard->scoring_mode,
            'result_mode' => $scoreboard->result_mode,
            'is_active' => $scoreboard->is_active,
            'show_progress_bar' => $scoreboard->show_progress_bar,
            'allow_back_navigation' => $scoreboard->allow_back_navigation,
        ];
    }

    private function questionTypeOptions(): array
    {
        return [
            ['value' => 'radio_buttons', 'label' => 'Radio Buttons'],
            ['value' => 'multiple_choice_buttons', 'label' => 'Multiple Choice Buttons'],
            ['value' => 'multiple_choice_checkboxes', 'label' => 'Multiple Choice Checkboxes'],
            ['value' => 'yes_no_maybe', 'label' => 'Yes / No / Maybe'],
            ['value' => 'image_button', 'label' => 'Image Button'],
            ['value' => 'sliding_scale', 'label' => 'Sliding Scale'],
            ['value' => 'linear_scale', 'label' => 'Linear Scale'],
            ['value' => 'divided_scale', 'label' => 'Divided Scale'],
            ['value' => 'numeric', 'label' => 'Numeric'],
            ['value' => 'open_text', 'label' => 'Open Text'],
            ['value' => 'info_screen', 'label' => 'Info Screen'],
        ];
    }
}
