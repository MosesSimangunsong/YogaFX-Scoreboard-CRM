<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Scoreboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by_user_id',
        'title',
        'slug',
        'description',
        'thumbnail_path',
        'status',
        'duration_minutes',
        'scoring_mode',
        'result_mode',
        'is_active',
        'show_progress_bar',
        'allow_back_navigation',
        'logo_path',
        'logo_max_width',
        'logo_alignment',
        'logo_link',
        'header_position',
        'section_background',
        'top_margin',
        'bottom_margin',
        'footer_content',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_progress_bar' => 'boolean',
        'allow_back_navigation' => 'boolean',
        'duration_minutes' => 'integer',
        'top_margin' => 'integer',
        'bottom_margin' => 'integer',
    ];

    protected $appends = [
        'thumbnail_url',
        'logo_url',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ScoreboardQuestion::class)->orderBy('sort_order');
    }

    public function resultRanges(): HasMany
    {
        return $this->hasMany(ScoreboardResultRange::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function accessLinks(): HasMany
    {
        return $this->hasMany(ParticipantAccessLink::class);
    }

    public function scopePubliclyAvailable($query)
    {
        return $query
            ->where('status', 'published')
            ->where('is_active', true);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::disk('public')->url($this->thumbnail_path) : null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function toDesignPayload(): array
    {
        return [
            'logo_url' => $this->logo_url,
            'logo_max_width' => $this->logo_max_width,
            'logo_alignment' => $this->logo_alignment,
            'logo_link' => $this->logo_link,
            'header_position' => $this->header_position,
            'section_background' => $this->section_background,
            'top_margin' => $this->top_margin,
            'bottom_margin' => $this->bottom_margin,
            'footer_content' => $this->footer_content,
        ];
    }
}
