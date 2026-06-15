<?php

namespace App\Support\Scoreboards;

use App\Models\ScoreboardQuestion;
use App\Models\Submission;
use App\Models\SubmissionAnswer;

class SubmissionMetrics
{
    public static function resolveTimeTaken(Submission $submission): array
    {
        $startedAt = $submission->started_at;
        $submittedAt = $submission->submitted_at;
        $completedAt = $submission->completed_at;

        $endTime = null;

        if ($startedAt && $submittedAt && $submittedAt->greaterThanOrEqualTo($startedAt)) {
            $endTime = $submittedAt;
        } elseif ($startedAt && $completedAt && $completedAt->greaterThanOrEqualTo($startedAt)) {
            $endTime = $completedAt;
        }

        if (! $startedAt || ! $endTime) {
            return [
                'seconds' => null,
                'display' => 'Unavailable',
            ];
        }

        $seconds = max(0, $endTime->diffInSeconds($startedAt));
        $hours = str_pad((string) intdiv($seconds, 3600), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((string) intdiv($seconds % 3600, 60), 2, '0', STR_PAD_LEFT);
        $remainingSeconds = str_pad((string) ($seconds % 60), 2, '0', STR_PAD_LEFT);

        return [
            'seconds' => $seconds,
            'display' => "{$hours} hours {$minutes} minutes {$remainingSeconds} seconds",
        ];
    }

    public static function questionIsGradable(ScoreboardQuestion $question): bool
    {
        return $question->options->contains('is_correct', true);
    }

    public static function answerIsCorrect(ScoreboardQuestion $question, SubmissionAnswer $answer): bool
    {
        if (! self::questionIsGradable($question)) {
            return false;
        }

        $correctOptionIds = $question->options
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();

        $selectedOptionIds = collect($answer->selected_option_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();

        return $correctOptionIds->values()->all() === $selectedOptionIds->values()->all();
    }
}
