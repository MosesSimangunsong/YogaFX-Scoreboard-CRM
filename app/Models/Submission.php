<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'scoreboard_id',
        'participant_id',
        'participant_access_link_id',
        'status',
        'started_at',
        'submitted_at',
        'completed_at',
        'current_question_id',
        'last_answered_question_id',
        'finished_reason',
        'last_answered_at',
        'overall_score',
        'score_payload',
        'tracking_payload',
        'progress_payload',
        'scoreboard_result_range_id',
        'result_title',
        'result_description',
        'result_recommendation',
        'scored_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_answered_at' => 'datetime',
        'overall_score' => 'decimal:2',
        'score_payload' => 'array',
        'tracking_payload' => 'array',
        'progress_payload' => 'array',
        'scored_at' => 'datetime',
    ];

    public function scoreboard(): BelongsTo
    {
        return $this->belongsTo(Scoreboard::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function accessLink(): BelongsTo
    {
        return $this->belongsTo(ParticipantAccessLink::class, 'participant_access_link_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class);
    }

    public function categoryScores(): HasMany
    {
        return $this->hasMany(SubmissionCategoryScore::class);
    }

    public function pdfReports(): HasMany
    {
        return $this->hasMany(PdfReport::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function resultRange(): BelongsTo
    {
        return $this->belongsTo(ScoreboardResultRange::class, 'scoreboard_result_range_id');
    }
}
