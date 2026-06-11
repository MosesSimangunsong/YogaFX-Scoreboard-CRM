<?php

namespace App\Services\Scoreboards;

use App\Models\Scoreboard;
use Illuminate\Validation\ValidationException;

class ValidateScoreboardPublishReadiness
{
    public function execute(?Scoreboard $scoreboard, array $attributes): void
    {
        $status = (string) ($attributes['status'] ?? $scoreboard?->status ?? 'draft');
        $isActive = (bool) ($attributes['is_active'] ?? $scoreboard?->is_active ?? false);
        $resultMode = (string) ($attributes['result_mode'] ?? $scoreboard?->result_mode ?? 'score_or_range');
        $messages = [];

        if ($isActive && $status !== 'published') {
            $messages['is_active'][] = 'Only published scoreboards can be active.';
        }

        if (! $this->needsPublishChecks($status, $isActive)) {
            if ($messages !== []) {
                throw ValidationException::withMessages($messages);
            }

            return;
        }

        if (! $scoreboard?->exists) {
            $messages['status'][] = 'Create this scoreboard as a draft first, then complete the builder before publishing.';

            throw ValidationException::withMessages($messages);
        }

        $scoreboard->loadMissing([
            'questions.options',
            'resultRanges',
        ]);

        if ($scoreboard->questions->isEmpty()) {
            $messages['status'][] = 'A published scoreboard must include at least one question.';
        }

        $hasOptionlessQuestion = $scoreboard->questions
            ->contains(fn ($question) => $this->questionRequiresOptions($question->question_type) && $question->options->isEmpty());

        if ($hasOptionlessQuestion) {
            $messages['status'][] = 'Every option-based question must include at least one answer option before publishing.';
        }

        if (
            $resultMode === 'range_only' &&
            $scoreboard->resultRanges->where('status', 'active')->isEmpty()
        ) {
            $messages['result_mode'][] = 'Range Only mode requires at least one active result range.';
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function needsPublishChecks(string $status, bool $isActive): bool
    {
        return $status === 'published' || $isActive;
    }

    private function questionRequiresOptions(string $questionType): bool
    {
        return in_array($questionType, [
            'yes_no_maybe',
            'multiple_choice_buttons',
            'multiple_choice_checkboxes',
            'radio_buttons',
            'image_button',
        ], true);
    }
}
