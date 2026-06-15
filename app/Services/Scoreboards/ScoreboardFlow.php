<?php

namespace App\Services\Scoreboards;

use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\ScoreboardQuestionOption;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use Illuminate\Support\Collection;

class ScoreboardFlow
{
    public function orderedQuestions(Scoreboard $scoreboard): Collection
    {
        return $scoreboard->questions
            ->sortBy('sort_order')
            ->values();
    }

    public function stableOptions(ScoreboardQuestion $question, Submission $submission): Collection
    {
        $options = $question->options
            ->sortBy('sort_order')
            ->values();

        if (! $question->randomize_answers_order) {
            return $options;
        }

        return $options
            ->sortBy(fn (ScoreboardQuestionOption $option) => hash('sha256', $submission->id.'|'.$question->id.'|'.$option->id))
            ->values();
    }

    public function activePath(Collection $questions, Submission $submission): Collection
    {
        $visitedIds = collect(data_get($submission->progress_payload, 'visited_question_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $currentQuestionId = $submission->current_question_id;

        if ($currentQuestionId) {
            $visitedIds->push((int) $currentQuestionId);
        }

        return $visitedIds
            ->unique()
            ->map(fn (int $questionId) => $questions->firstWhere('id', $questionId))
            ->filter()
            ->values();
    }

    public function nextQuestionId(
        Collection $questions,
        ScoreboardQuestion $question,
        ?SubmissionAnswer $answer,
    ): ?int {
        $answerLevelTarget = $this->resolveAnswerJumpTarget($question, $answer);

        if ($answerLevelTarget !== null && $questions->contains(fn (ScoreboardQuestion $item) => $item->id === $answerLevelTarget)) {
            return $answerLevelTarget;
        }

        if (
            $question->jump_enabled &&
            $question->jump_to_question_id &&
            $questions->contains(fn (ScoreboardQuestion $item) => $item->id === $question->jump_to_question_id)
        ) {
            return $question->jump_to_question_id;
        }

        $orderedIds = $questions->pluck('id')->values();
        $currentIndex = $orderedIds->search($question->id);

        if ($currentIndex === false || $currentIndex >= ($orderedIds->count() - 1)) {
            return null;
        }

        return $orderedIds[$currentIndex + 1];
    }

    public function appendVisitedQuestion(Submission $submission, int $questionId): array
    {
        return collect(data_get($submission->progress_payload, 'visited_question_ids', []))
            ->push($questionId)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function resolveAnswerJumpTarget(
        ScoreboardQuestion $question,
        ?SubmissionAnswer $answer,
    ): ?int {
        if (! $answer || ! $this->questionSupportsAnswerJump($question)) {
            return null;
        }

        $selectedOptionIds = collect($answer->selected_option_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($selectedOptionIds->isEmpty()) {
            return null;
        }

        $firstJumpOption = $question->options
            ->whereIn('id', $selectedOptionIds->all())
            ->sortBy('sort_order')
            ->first(fn (ScoreboardQuestionOption $option) => $option->jump_enabled && $option->jump_to_question_id);

        return $firstJumpOption?->jump_to_question_id;
    }

    private function questionSupportsAnswerJump(ScoreboardQuestion $question): bool
    {
        if ($question->question_type === 'multiple_choice_checkboxes') {
            return false;
        }

        return in_array($question->question_type, [
            'yes_no_maybe',
            'multiple_choice_buttons',
            'radio_buttons',
            'image_button',
        ], true);
    }
}
