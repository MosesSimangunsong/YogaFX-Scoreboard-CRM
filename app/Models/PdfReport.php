<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PdfReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'disk',
        'path',
        'file_name',
        'mime_type',
        'status',
        'generation_error',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path || ! $this->disk) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
