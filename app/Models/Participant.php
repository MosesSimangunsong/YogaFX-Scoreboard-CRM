<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'whatsapp',
        'country',
        'tracking_payload',
        'source_type',
        'status',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'tracking_payload' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function accessLinks(): HasMany
    {
        return $this->hasMany(ParticipantAccessLink::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
