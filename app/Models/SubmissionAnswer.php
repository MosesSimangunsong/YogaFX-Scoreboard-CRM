<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'scoreboard_question_id',
        'scoreboard_question_option_id',
        'selected_option_ids',
        'answer_text',
        'answer_number',
        'answer_payload',
        'question_snapshot',
        'answered_at',
    ];

    protected $casts = [
        'selected_option_ids' => 'array',
        'answer_payload' => 'array',
        'question_snapshot' => 'array',
        'answer_number' => 'decimal:2',
        'answered_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ScoreboardQuestion::class, 'scoreboard_question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(ScoreboardQuestionOption::class, 'scoreboard_question_option_id');
    }
}
