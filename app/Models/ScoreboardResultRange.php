<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreboardResultRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'scoreboard_id',
        'title',
        'description',
        'recommendation',
        'min_score',
        'max_score',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function scoreboard(): BelongsTo
    {
        return $this->belongsTo(Scoreboard::class);
    }
}
