<?php

namespace App\Services\Delivery;

use App\Models\PdfReport;
use App\Models\Submission;
use Illuminate\Support\Facades\Storage;

class GenerateSubmissionPdfReport
{
    public function execute(Submission $submission, PdfReport $pdfReport): PdfReport
    {
        $submission->loadMissing([
            'participant',
            'scoreboard',
            'categoryScores',
            'resultRange',
        ]);

        $text = view('reports.submission-result-pdf', [
            'submission' => $submission,
            'participant' => $submission->participant,
            'scoreboard' => $submission->scoreboard,
            'result' => $submission->resultRange,
            'categoryScores' => $submission->categoryScores,
        ])->render();

        $pdfBinary = $this->buildPdfFromText($text);
        $relativePath = 'reports/submissions/'.$submission->id.'/'.$pdfReport->file_name;

        Storage::disk($pdfReport->disk)->put($relativePath, $pdfBinary);

        $pdfReport->update([
            'path' => $relativePath,
            'status' => 'generated',
            'generated_at' => now(),
            'generation_error' => null,
        ]);

        return $pdfReport->fresh();
    }

    private function buildPdfFromText(string $text): string
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($text)) ?: [];
        $normalizedLines = collect($lines)
            ->map(fn ($line) => trim(preg_replace('/\s+/', ' ', $line) ?? ''))
            ->filter()
            ->values()
            ->all();

        if ($normalizedLines === []) {
            $normalizedLines = ['Submission report'];
        }

        $chunks = array_chunk($normalizedLines, 42);
        $objects = [];
        $pageObjectIds = [];
        $nextObjectId = 3;

        foreach ($chunks as $chunk) {
            $pageObjectId = $nextObjectId++;
            $fontObjectId = $nextObjectId++;
            $contentObjectId = $nextObjectId++;

            $pageObjectIds[] = $pageObjectId;

            $contentStream = $this->buildPageContentStream($chunk);

            $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 {$fontObjectId} 0 R >> >> /Contents {$contentObjectId} 0 R >>";
            $objects[$fontObjectId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
            $objects[$contentObjectId] = "<< /Length ".strlen($contentStream)." >>\nstream\n{$contentStream}\nendstream";
        }

        ksort($objects);

        $kids = collect($pageObjectIds)->map(fn ($id) => "{$id} 0 R")->implode(' ');

        $baseObjects = [
            1 => "<< /Type /Catalog /Pages 2 0 R >>",
            2 => "<< /Type /Pages /Kids [ {$kids} ] /Count ".count($pageObjectIds)." >>",
        ];

        $allObjects = $baseObjects + $objects;
        ksort($allObjects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($allObjects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($allObjects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_keys($allObjects) as $id) {
            $pdf .= str_pad((string) $offsets[$id], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size ".(count($allObjects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function buildPageContentStream(array $lines): string
    {
        $content = "BT\n/F1 12 Tf\n50 760 Td\n16 TL\n";

        foreach (array_values($lines) as $index => $line) {
            $escaped = str_replace(
                ['\\', '(', ')'],
                ['\\\\', '\(', '\)'],
                $line,
            );

            $content .= $index === 0
                ? "({$escaped}) Tj\n"
                : "T*\n({$escaped}) Tj\n";
        }

        $content .= "ET";

        return $content;
    }
}
