<?php

namespace App\Services\Scoring;

use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\ScoreboardResultRange;
use App\Models\Submission;
use App\Support\Scoreboards\SubmissionMetrics;
use Illuminate\Support\Collection;

class ScoreSubmission
{
    public function execute(Submission $submission): Submission
    {
        $submission->load([
            'scoreboard.resultRanges',
            'answers.question.options',
            'categoryScores',
        ]);

        $categoryScores = [];
        $overallScore = 0.0;
        $gradableQuestionsCount = 0;
        $correctAnswersCount = 0;

        foreach ($submission->answers as $answer) {
            $question = $answer->question;

            if (! $question) {
                continue;
            }

            if (SubmissionMetrics::questionIsGradable($question)) {
                $gradableQuestionsCount++;

                if (SubmissionMetrics::answerIsCorrect($question, $answer)) {
                    $correctAnswersCount++;
                }
            }

            $answerScore = $this->resolveAnswerScore($question, $answer);

            if ($answerScore === null) {
                continue;
            }

            $overallScore += $answerScore;

            $categoryKey = $this->resolveCategoryKey($question);

            if (! $categoryKey) {
                continue;
            }

            if (! isset($categoryScores[$categoryKey])) {
                $categoryScores[$categoryKey] = [
                    'score' => 0.0,
                    'answered_questions_count' => 0,
                ];
            }

            $categoryScores[$categoryKey]['score'] += $answerScore;
            $categoryScores[$categoryKey]['answered_questions_count']++;
        }

        $percentage = $gradableQuestionsCount > 0
            ? round(($correctAnswersCount / $gradableQuestionsCount) * 100, 2)
            : null;

        $matchedRange = $this->matchResultRange(
            $submission->scoreboard,
            $overallScore,
        );

        $submission->categoryScores()->delete();

        foreach ($categoryScores as $categoryKey => $payload) {
            $submission->categoryScores()->create([
                'category_key' => $categoryKey,
                'score' => round($payload['score'], 2),
                'answered_questions_count' => $payload['answered_questions_count'],
            ]);
        }

        $submission->update([
            'overall_score' => round($overallScore, 2),
            'score_payload' => [
                'result_mode' => $submission->scoreboard->result_mode,
                'category_keys' => array_keys($categoryScores),
                'answered_questions_count' => $submission->answers->count(),
                'gradable_questions_count' => $gradableQuestionsCount,
                'correct_answers_count' => $correctAnswersCount,
                'percentage' => $percentage,
            ],
            'scoreboard_result_range_id' => $matchedRange?->id,
            'result_title' => $matchedRange?->title,
            'result_description' => $matchedRange?->description,
            'result_recommendation' => $matchedRange?->recommendation,
            'scored_at' => now(),
        ]);

        return $submission->fresh([
            'scoreboard',
            'categoryScores',
            'resultRange',
            'answers',
        ]);
    }

    private function resolveAnswerScore(ScoreboardQuestion $question, $answer): ?float
    {
        $optionBasedTypes = [
            'yes_no_maybe',
            'radio_buttons',
            'multiple_choice_buttons',
            'multiple_choice_checkboxes',
            'image_button',
        ];

        if (in_array($question->question_type, $optionBasedTypes, true)) {
            return $question->options
                ->whereIn('id', $answer->selected_option_ids ?? [])
                ->filter(fn ($option) => $option->scoring_enabled && $option->score_value !== null)
                ->sum(fn ($option) => (float) $option->score_value);
        }

        if (in_array($question->question_type, ['numeric', 'sliding_scale', 'linear_scale', 'divided_scale'], true)) {
            return $answer->answer_number !== null ? (float) $answer->answer_number : null;
        }

        return null;
    }

    private function resolveCategoryKey(ScoreboardQuestion $question): ?string
    {
        $value = trim((string) $question->scoring_category);

        if ($value === '' || $value === 'overall_only') {
            return null;
        }

        return $value;
    }

    private function matchResultRange(Scoreboard $scoreboard, float $overallScore): ?ScoreboardResultRange
    {
        /** @var Collection<int, ScoreboardResultRange> $ranges */
        $ranges = $scoreboard->resultRanges
            ->where('status', 'active')
            ->sortBy('sort_order')
            ->values();

        return $ranges->first(function (ScoreboardResultRange $range) use ($overallScore) {
            $minPass = $range->min_score === null || $overallScore >= (float) $range->min_score;
            $maxPass = $range->max_score === null || $overallScore <= (float) $range->max_score;

            return $minPass && $maxPass;
        });
    }
}
