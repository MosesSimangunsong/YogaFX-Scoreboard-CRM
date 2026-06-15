<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\ScoreboardQuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ScoreboardOptionController extends Controller
{
    public function store(Scoreboard $assessment, ScoreboardQuestion $question): RedirectResponse
    {
        abort_unless($question->scoreboard_id === $assessment->id, 404);

        $question->options()->create([
            'label' => 'Answer '.(($question->options()->count()) + 1),
            'sort_order' => ($question->options()->max('sort_order') ?? 0) + 1,
        ]);

        return redirect()
            ->route('admin.scoreboards.builder', [
                'assessment' => $assessment,
                'question' => $question,
            ])
            ->with('status', 'scoreboard-option-created');
    }

    public function update(Request $request, Scoreboard $assessment, ScoreboardQuestion $question, ScoreboardQuestionOption $option): RedirectResponse
    {
        abort_unless($question->scoreboard_id === $assessment->id && $option->question_id === $question->id, 404);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'internal_value' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_correct' => ['required', 'boolean'],
            'scoring_enabled' => ['required', 'boolean'],
            'score_value' => ['nullable', 'numeric'],
            'jump_enabled' => ['required', 'boolean'],
            'jump_to_question_id' => [
                'nullable',
                Rule::exists('scoreboard_questions', 'id')->where(
                    fn ($query) => $query->where('scoreboard_id', $assessment->id),
                ),
            ],
            'is_other_option' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        if (! $validated['scoring_enabled']) {
            $validated['score_value'] = null;
        }

        if (
            $question->question_type === 'multiple_choice_checkboxes' ||
            ! in_array($question->question_type, ['yes_no_maybe', 'multiple_choice_buttons', 'radio_buttons', 'image_button'], true) ||
            ! $validated['jump_enabled']
        ) {
            $validated['jump_enabled'] = false;
            $validated['jump_to_question_id'] = null;
        }

        if ($option->is_other_option) {
            $validated['is_other_option'] = true;
        }

        if ($request->hasFile('image')) {
            if ($option->image_path) {
                Storage::disk('public')->delete($option->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('scoreboards/options', 'public');
        }

        unset($validated['image']);

        $option->update($validated);

        return redirect()
            ->route('admin.scoreboards.builder', [
                'assessment' => $assessment,
                'question' => $question,
            ])
            ->with('status', 'scoreboard-option-saved');
    }

    public function destroy(Scoreboard $assessment, ScoreboardQuestion $question, ScoreboardQuestionOption $option): RedirectResponse
    {
        abort_unless($question->scoreboard_id === $assessment->id && $option->question_id === $question->id, 404);

        if ($option->image_path) {
            Storage::disk('public')->delete($option->image_path);
        }

        $option->delete();

        return redirect()
            ->route('admin.scoreboards.builder', [
                'assessment' => $assessment,
                'question' => $question,
            ])
            ->with('status', 'scoreboard-option-deleted');
    }
}
