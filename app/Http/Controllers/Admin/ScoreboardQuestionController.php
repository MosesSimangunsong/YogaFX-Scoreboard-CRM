<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\ScoreboardQuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScoreboardQuestionController extends Controller
{
    public function store(Scoreboard $assessment): RedirectResponse
    {
        $question = $assessment->questions()->create([
            'question_type' => 'radio_buttons',
            'sort_order' => ($assessment->questions()->max('sort_order') ?? 0) + 1,
            'required' => true,
            'show_maybe_answer' => true,
            'show_labels' => true,
            'scoring_category' => 'overall_only',
        ]);

        return redirect()
            ->route('admin.scoreboards.builder', [
                'assessment' => $assessment,
                'question' => $question,
            ])
            ->with('status', 'scoreboard-question-created');
    }

    public function update(Request $request, Scoreboard $assessment, ScoreboardQuestion $question): RedirectResponse
    {
        abort_unless($question->scoreboard_id === $assessment->id, 404);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'question_text' => ['nullable', 'string'],
            'question_type' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'show_instruction' => ['required', 'boolean'],
            'instruction_text' => ['nullable', 'string'],
            'required' => ['required', 'boolean'],
            'randomize_answers_order' => ['required', 'boolean'],
            'jump_enabled' => ['required', 'boolean'],
            'jump_to_question_id' => [
                'nullable',
                Rule::exists('scoreboard_questions', 'id')->where(
                    fn ($query) => $query->where('scoreboard_id', $assessment->id),
                ),
            ],
            'show_maybe_answer' => ['required', 'boolean'],
            'allow_multi_select' => ['required', 'boolean'],
            'min_count' => ['nullable', 'integer', 'min:0'],
            'max_count' => ['nullable', 'integer', 'min:0'],
            'allow_other_option' => ['required', 'boolean'],
            'show_labels' => ['required', 'boolean'],
            'score_range_min' => ['nullable', 'numeric'],
            'score_range_max' => ['nullable', 'numeric'],
            'starting_score' => ['nullable', 'numeric'],
            'section_count' => ['nullable', 'integer', 'min:0'],
            'allow_decimals' => ['required', 'boolean'],
            'input_type' => ['nullable', 'string', 'max:255'],
            'character_limit' => ['nullable', 'integer', 'min:0'],
            'show_score_tooltip' => ['required', 'boolean'],
            'score_tooltip_format' => ['nullable', 'string', 'max:255'],
            'answer_image_fit' => ['nullable', 'string', 'max:255'],
            'answers_per_row' => ['nullable', 'integer', 'min:1', 'max:6'],
            'scoring_category' => ['nullable', 'string', 'max:255'],
            'left_label' => ['nullable', 'string', 'max:255'],
            'center_label' => ['nullable', 'string', 'max:255'],
            'right_label' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $validated['jump_enabled']) {
            $validated['jump_to_question_id'] = null;
        }

        if (! $validated['show_instruction']) {
            $validated['instruction_text'] = null;
        }

        $question->update($validated);

        $this->syncFixedOptions($question->fresh());

        return redirect()
            ->route('admin.scoreboards.builder', [
                'assessment' => $assessment,
                'question' => $question,
            ])
            ->with('status', 'scoreboard-question-saved');
    }

    public function destroy(Scoreboard $assessment, ScoreboardQuestion $question): RedirectResponse
    {
        abort_unless($question->scoreboard_id === $assessment->id, 404);

        $question->delete();

        $remainingQuestion = $assessment->questions()->orderBy('sort_order')->first();

        return redirect()
            ->route('admin.scoreboards.builder', [
                'assessment' => $assessment,
                'question' => $remainingQuestion?->id,
            ])
            ->with('status', 'scoreboard-question-deleted');
    }

    private function syncFixedOptions(ScoreboardQuestion $question): void
    {
        if ($question->question_type === 'yes_no_maybe') {
            $fixed = [
                ['label' => 'Yes', 'internal_value' => 'yes', 'sort_order' => 1],
                ['label' => 'No', 'internal_value' => 'no', 'sort_order' => 2],
                ['label' => 'Maybe', 'internal_value' => 'maybe', 'sort_order' => 3],
            ];

            foreach ($fixed as $item) {
                ScoreboardQuestionOption::query()->firstOrCreate(
                    [
                        'question_id' => $question->id,
                        'internal_value' => $item['internal_value'],
                    ],
                    [
                        'label' => $item['label'],
                        'sort_order' => $item['sort_order'],
                        'is_fixed_option' => true,
                    ],
                );
            }
        }

        if (
            $question->question_type === 'multiple_choice_buttons' &&
            $question->allow_other_option
        ) {
            ScoreboardQuestionOption::query()->firstOrCreate(
                [
                    'question_id' => $question->id,
                    'internal_value' => 'other',
                ],
                [
                    'label' => 'Other',
                    'sort_order' => ($question->options()->max('sort_order') ?? 0) + 1,
                    'is_other_option' => true,
                    'is_fixed_option' => true,
                ],
            );
        }
    }
}
