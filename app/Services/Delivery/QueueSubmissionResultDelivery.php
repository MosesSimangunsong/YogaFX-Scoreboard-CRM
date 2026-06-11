<?php

namespace App\Services\Delivery;

use App\Jobs\DeliverSubmissionResultJob;
use App\Models\EmailLog;
use App\Models\PdfReport;
use App\Models\Submission;

class QueueSubmissionResultDelivery
{
    public function execute(Submission $submission): array
    {
        $submission->loadMissing(['participant', 'scoreboard']);

        $latestEmailLog = $submission->emailLogs()
            ->with('pdfReport')
            ->latest('id')
            ->first();

        if ($latestEmailLog && in_array($latestEmailLog->status, ['queued', 'sending', 'sent'], true)) {
            $pdfReport = $latestEmailLog->pdfReport ?: $submission->pdfReports()->latest('id')->first();

            return [$pdfReport, $latestEmailLog];
        }

        $pdfReport = $submission->pdfReports()->create([
            'disk' => config('filesystems.default'),
            'file_name' => $this->buildFileName($submission),
            'mime_type' => 'application/pdf',
            'status' => 'queued',
        ]);

        $emailLog = $submission->emailLogs()->create([
            'pdf_report_id' => $pdfReport->id,
            'recipient_email' => $submission->participant->email,
            'subject' => 'Your '.$submission->scoreboard->title.' result is ready',
            'mailer' => config('mail.default'),
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        DeliverSubmissionResultJob::dispatch($submission->id, $pdfReport->id, $emailLog->id);

        return [$pdfReport, $emailLog];
    }

    private function buildFileName(Submission $submission): string
    {
        $slug = str($submission->scoreboard->slug ?: 'scoreboard')->slug()->value();

        return $slug.'-submission-'.$submission->id.'.pdf';
    }
}
