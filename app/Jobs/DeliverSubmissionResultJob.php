<?php

namespace App\Jobs;

use App\Mail\SubmissionResultMail;
use App\Models\EmailLog;
use App\Models\PdfReport;
use App\Models\Submission;
use App\Services\Delivery\GenerateSubmissionPdfReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DeliverSubmissionResultJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $submissionId,
        public int $pdfReportId,
        public int $emailLogId,
    ) {
    }

    public function handle(GenerateSubmissionPdfReport $generateSubmissionPdfReport): void
    {
        $submission = Submission::query()
            ->with(['participant', 'scoreboard', 'categoryScores', 'resultRange'])
            ->findOrFail($this->submissionId);
        $pdfReport = PdfReport::query()->findOrFail($this->pdfReportId);
        $emailLog = EmailLog::query()->findOrFail($this->emailLogId);

        try {
            $pdfReport->update([
                'status' => 'generating',
                'generation_error' => null,
            ]);

            $pdfReport = $generateSubmissionPdfReport->execute($submission, $pdfReport);

            $emailLog->update([
                'status' => 'sending',
                'error_message' => null,
            ]);

            Mail::to($emailLog->recipient_email)->send(
                new SubmissionResultMail($submission, $pdfReport, $emailLog->subject),
            );

            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $throwable) {
            $pdfStatus = $pdfReport->status === 'generated' ? 'generated' : 'failed';

            $pdfReport->update([
                'status' => $pdfStatus,
                'generation_error' => $pdfStatus === 'failed' ? $throwable->getMessage() : $pdfReport->generation_error,
            ]);

            $emailLog->update([
                'status' => 'failed',
                'error_message' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }
}
