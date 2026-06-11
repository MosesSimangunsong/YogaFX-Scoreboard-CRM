<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'pdf_report_id',
        'recipient_email',
        'subject',
        'mailer',
        'status',
        'queued_at',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function pdfReport(): BelongsTo
    {
        return $this->belongsTo(PdfReport::class);
    }
}
