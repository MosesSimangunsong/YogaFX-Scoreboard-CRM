<?php

namespace App\Mail;

use App\Models\PdfReport;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public PdfReport $pdfReport,
        public string $subjectLine,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.submission-result',
        );
    }

    public function attachments(): array
    {
        if (! $this->pdfReport->path || ! $this->pdfReport->disk) {
            return [];
        }

        return [
            Attachment::fromStorageDisk(
                $this->pdfReport->disk,
                $this->pdfReport->path,
            )->as($this->pdfReport->file_name ?: 'result-report.pdf')
                ->withMime($this->pdfReport->mime_type ?: 'application/pdf'),
        ];
    }
}
