<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionCategoryScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'category_key',
        'score',
        'answered_questions_count',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'answered_questions_count' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
