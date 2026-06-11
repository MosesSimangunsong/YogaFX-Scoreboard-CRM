<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoreboardQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'scoreboard_id',
        'title',
        'question_text',
        'question_type',
        'sort_order',
        'show_instruction',
        'instruction_text',
        'required',
        'randomize_answers_order',
        'jump_enabled',
        'jump_to_question_id',
        'show_maybe_answer',
        'allow_multi_select',
        'min_count',
        'max_count',
        'allow_other_option',
        'show_labels',
        'score_range_min',
        'score_range_max',
        'starting_score',
        'section_count',
        'allow_decimals',
        'input_type',
        'character_limit',
        'show_score_tooltip',
        'score_tooltip_format',
        'answer_image_fit',
        'answers_per_row',
        'scoring_category',
        'left_label',
        'center_label',
        'right_label',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'show_instruction' => 'boolean',
        'required' => 'boolean',
        'randomize_answers_order' => 'boolean',
        'jump_enabled' => 'boolean',
        'show_maybe_answer' => 'boolean',
        'allow_multi_select' => 'boolean',
        'allow_other_option' => 'boolean',
        'show_labels' => 'boolean',
        'allow_decimals' => 'boolean',
        'show_score_tooltip' => 'boolean',
        'min_count' => 'integer',
        'max_count' => 'integer',
        'section_count' => 'integer',
        'character_limit' => 'integer',
        'answers_per_row' => 'integer',
        'score_range_min' => 'decimal:2',
        'score_range_max' => 'decimal:2',
        'starting_score' => 'decimal:2',
    ];

    public function scoreboard(): BelongsTo
    {
        return $this->belongsTo(Scoreboard::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ScoreboardQuestionOption::class, 'question_id')->orderBy('sort_order');
    }
}
