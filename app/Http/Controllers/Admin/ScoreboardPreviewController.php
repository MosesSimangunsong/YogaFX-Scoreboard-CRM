<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use Inertia\Inertia;
use Inertia\Response;

class ScoreboardPreviewController extends Controller
{
    public function __invoke(Scoreboard $assessment): Response
    {
        $assessment->load([
            'questions.options',
            'resultRanges',
        ]);

        return Inertia::render('Admin/Scoreboards/Preview', [
            'scoreboard' => [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'description' => $assessment->description,
                'show_progress_bar' => $assessment->show_progress_bar,
                'allow_back_navigation' => $assessment->allow_back_navigation,
                'duration_minutes' => $assessment->duration_minutes,
                'result_mode' => $assessment->result_mode,
            ],
            'questions' => $assessment->questions
                ->sortBy('sort_order')
                ->values()
                ->map(fn (ScoreboardQuestion $question) => [
                    'id' => $question->id,
                    'title' => $question->title,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'sort_order' => $question->sort_order,
                    'show_instruction' => $question->show_instruction,
                    'instruction_text' => $question->instruction_text,
                    'required' => $question->required,
                    'randomize_answers_order' => $question->randomize_answers_order,
                    'jump_enabled' => $question->jump_enabled,
                    'jump_to_question_id' => $question->jump_to_question_id,
                    'show_maybe_answer' => $question->show_maybe_answer,
                    'allow_multi_select' => $question->allow_multi_select,
                    'min_count' => $question->min_count,
                    'max_count' => $question->max_count,
                    'allow_other_option' => $question->allow_other_option,
                    'show_labels' => $question->show_labels,
                    'score_range_min' => $question->score_range_min,
                    'score_range_max' => $question->score_range_max,
                    'starting_score' => $question->starting_score,
                    'section_count' => $question->section_count,
                    'allow_decimals' => $question->allow_decimals,
                    'input_type' => $question->input_type,
                    'character_limit' => $question->character_limit,
                    'show_score_tooltip' => $question->show_score_tooltip,
                    'score_tooltip_format' => $question->score_tooltip_format,
                    'answer_image_fit' => $question->answer_image_fit,
                    'answers_per_row' => $question->answers_per_row,
                    'scoring_category' => $question->scoring_category,
                    'left_label' => $question->left_label,
                    'center_label' => $question->center_label,
                    'right_label' => $question->right_label,
                    'options' => $question->options
                        ->filter(fn ($option) => $question->question_type !== 'yes_no_maybe' || $question->show_maybe_answer || $option->internal_value !== 'maybe')
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
                            'image_url' => $option->image_url,
                        ]),
                ]),
            'resultRanges' => $assessment->resultRanges
                ->where('status', 'active')
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($range) => [
                    'id' => $range->id,
                    'title' => $range->title,
                    'description' => $range->description,
                    'min_score' => $range->min_score,
                    'max_score' => $range->max_score,
                ]),
        ]);
    }
}
