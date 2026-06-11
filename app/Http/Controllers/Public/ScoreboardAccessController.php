<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ParticipantAccessLink;
use Inertia\Inertia;
use Inertia\Response;

class ScoreboardAccessController extends Controller
{
    public function __invoke(string $accessCode): Response
    {
        $accessLink = ParticipantAccessLink::query()
            ->with(['participant', 'scoreboard'])
            ->where('access_code', $accessCode)
            ->resolvable()
            ->firstOrFail();

        $accessLink->markAsAccessed();

        return Inertia::render('Public/Scoreboards/Access', [
            'accessLink' => [
                'code' => $accessLink->access_code,
                'status' => $accessLink->status,
                'issued_at' => optional($accessLink->issued_at)->toIso8601String(),
                'first_accessed_at' => optional($accessLink->first_accessed_at)->toIso8601String(),
                'last_accessed_at' => optional($accessLink->last_accessed_at)->toIso8601String(),
            ],
            'scoreboard' => [
                'title' => $accessLink->scoreboard->title,
                'slug' => $accessLink->scoreboard->slug,
                'description' => $accessLink->scoreboard->description,
            ],
            'participant' => [
                'first_name' => $accessLink->participant->first_name,
                'last_name' => $accessLink->participant->last_name,
                'email' => $accessLink->participant->email,
            ],
            'assessment' => [
                'start_url' => route('public.scoreboards.assessment.show', $accessLink->access_code),
                'completed_url' => route('public.scoreboards.completed', $accessLink->access_code),
                'has_submitted_submission' => $accessLink->submissions()
                    ->where('status', 'submitted')
                    ->exists(),
            ],
        ]);
    }
}
