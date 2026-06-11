<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use App\Models\Submission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'scoreboard_id' => $request->integer('scoreboard_id') ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        $submissions = Submission::query()
            ->with([
                'scoreboard',
                'participant',
                'resultRange',
                'pdfReports' => fn ($query) => $query->latest('id'),
                'emailLogs' => fn ($query) => $query->latest('id'),
            ])
            ->withCount('answers')
            ->when($filters['search'], function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested
                        ->whereHas('participant', function ($participantQuery) use ($search) {
                            $participantQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        })
                        ->orWhereHas('scoreboard', fn ($scoreboardQuery) => $scoreboardQuery->where('title', 'like', "%{$search}%"))
                        ->orWhere('result_title', 'like', "%{$search}%");
                });
            })
            ->when($filters['scoreboard_id'], fn ($query, int $scoreboardId) => $query->where('scoreboard_id', $scoreboardId))
            ->when($filters['status'], fn ($query, string $status) => $query->where('status', $status))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Submission $submission) => [
                'id' => $submission->id,
                'status' => $submission->status,
                'scoreboard' => [
                    'id' => $submission->scoreboard?->id,
                    'title' => $submission->scoreboard?->title,
                ],
                'participant' => [
                    'full_name' => trim(($submission->participant?->first_name ?? '').' '.($submission->participant?->last_name ?? '')),
                    'email' => $submission->participant?->email,
                    'whatsapp' => $submission->participant?->whatsapp,
                    'source_type' => $submission->participant?->source_type,
                ],
                'answers_count' => $submission->answers_count,
                'overall_score' => $submission->overall_score,
                'result_title' => $submission->result_title,
                'submitted_at' => optional($submission->submitted_at)->diffForHumans(),
                'delivery' => [
                    'pdf_status' => $submission->pdfReports->first()?->status,
                    'email_status' => $submission->emailLogs->first()?->status,
                ],
                'tracking' => [
                    'has_tracking' => filled($submission->tracking_payload),
                ],
            ]);

        return Inertia::render('Admin/Submissions/Index', [
            'submissions' => $submissions,
            'scoreboards' => Scoreboard::query()
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn (Scoreboard $scoreboard) => [
                    'id' => $scoreboard->id,
                    'title' => $scoreboard->title,
                ]),
            'filters' => $filters,
        ]);
    }

    public function show(Submission $submission): Response
    {
        $submission->load([
            'scoreboard',
            'participant',
            'accessLink',
            'resultRange',
            'categoryScores',
            'pdfReports' => fn ($query) => $query->latest('id'),
            'emailLogs' => fn ($query) => $query->latest('id'),
            'answers.question',
            'answers.selectedOption',
        ]);

        return Inertia::render('Admin/Submissions/Show', [
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'answers_count' => $submission->answers->count(),
                'overall_score' => $submission->overall_score,
                'result_title' => $submission->result_title,
                'result_description' => $submission->result_description,
                'result_recommendation' => $submission->result_recommendation,
                'submitted_at' => optional($submission->submitted_at)->toIso8601String(),
                'scored_at' => optional($submission->scored_at)->toIso8601String(),
                'scoreboard' => [
                    'id' => $submission->scoreboard?->id,
                    'title' => $submission->scoreboard?->title,
                    'slug' => $submission->scoreboard?->slug,
                ],
                'participant' => [
                    'id' => $submission->participant?->id,
                    'first_name' => $submission->participant?->first_name,
                    'last_name' => $submission->participant?->last_name,
                    'email' => $submission->participant?->email,
                    'whatsapp' => $submission->participant?->whatsapp,
                    'country' => $submission->participant?->country,
                    'source_type' => $submission->participant?->source_type,
                ],
                'access_link' => [
                    'code' => $submission->accessLink?->access_code,
                    'status' => $submission->accessLink?->status,
                    'url' => $submission->accessLink?->access_url,
                ],
                'tracking_payload' => $submission->tracking_payload ?? [],
            ],
            'categoryScores' => $submission->categoryScores
                ->sortByDesc('score')
                ->values()
                ->map(fn ($item) => [
                    'category_key' => $item->category_key,
                    'score' => $item->score,
                    'answered_questions_count' => $item->answered_questions_count,
                ]),
            'answers' => $submission->answers
                ->sortBy(fn ($answer) => data_get($answer->question_snapshot, 'sort_order', PHP_INT_MAX))
                ->values()
                ->map(fn ($answer) => [
                    'id' => $answer->id,
                    'question_title' => data_get($answer->question_snapshot, 'title')
                        ?: $answer->question?->title
                        ?: 'Untitled Question',
                    'question_text' => data_get($answer->question_snapshot, 'question_text')
                        ?: $answer->question?->question_text,
                    'question_type' => data_get($answer->question_snapshot, 'question_type')
                        ?: $answer->question?->question_type,
                    'scoring_category' => data_get($answer->question_snapshot, 'scoring_category'),
                    'selected_options' => collect(data_get($answer->answer_payload, 'selected_options', []))
                        ->map(fn ($option) => [
                            'label' => $option['label'] ?? $option['internal_value'] ?? 'Option',
                            'score_value' => $option['score_value'] ?? null,
                            'is_other_option' => $option['is_other_option'] ?? false,
                        ])
                        ->values(),
                    'other_text' => data_get($answer->answer_payload, 'other_text'),
                    'answer_text' => $answer->answer_text,
                    'answer_number' => $answer->answer_number,
                    'answered_at' => optional($answer->answered_at)->toIso8601String(),
                ]),
            'delivery' => [
                'pdf_reports' => $submission->pdfReports->map(fn ($report) => [
                    'id' => $report->id,
                    'status' => $report->status,
                    'file_name' => $report->file_name,
                    'generated_at' => optional($report->generated_at)->toIso8601String(),
                    'generation_error' => $report->generation_error,
                ])->values(),
                'email_logs' => $submission->emailLogs->map(fn ($emailLog) => [
                    'id' => $emailLog->id,
                    'recipient_email' => $emailLog->recipient_email,
                    'subject' => $emailLog->subject,
                    'mailer' => $emailLog->mailer,
                    'status' => $emailLog->status,
                    'queued_at' => optional($emailLog->queued_at)->toIso8601String(),
                    'sent_at' => optional($emailLog->sent_at)->toIso8601String(),
                    'error_message' => $emailLog->error_message,
                ])->values(),
            ],
        ]);
    }
}
