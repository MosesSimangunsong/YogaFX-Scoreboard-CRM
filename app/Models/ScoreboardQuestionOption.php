<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ScoreboardQuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'label',
        'internal_value',
        'image_path',
        'sort_order',
        'is_correct',
        'scoring_enabled',
        'score_value',
        'jump_enabled',
        'jump_to_question_id',
        'is_other_option',
        'is_fixed_option',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_correct' => 'boolean',
        'scoring_enabled' => 'boolean',
        'jump_enabled' => 'boolean',
        'is_other_option' => 'boolean',
        'is_fixed_option' => 'boolean',
        'score_value' => 'decimal:2',
    ];

    protected $appends = [
        'image_url',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(ScoreboardQuestion::class, 'question_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
