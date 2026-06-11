<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParticipantAccessLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'scoreboard_id',
        'participant_id',
        'access_code',
        'status',
        'source_type',
        'issued_at',
        'first_accessed_at',
        'last_accessed_at',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'first_accessed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $appends = [
        'access_url',
    ];

    public function scoreboard(): BelongsTo
    {
        return $this->belongsTo(Scoreboard::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function scopeResolvable($query)
    {
        return $query
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereHas('scoreboard', fn ($scoreboardQuery) => $scoreboardQuery->publiclyAvailable());
    }

    public function markAsAccessed(): void
    {
        $this->forceFill([
            'first_accessed_at' => $this->first_accessed_at ?? now(),
            'last_accessed_at' => now(),
        ])->save();
    }

    public function getAccessUrlAttribute(): string
    {
        return route('public.scoreboards.access', $this->access_code);
    }
}
