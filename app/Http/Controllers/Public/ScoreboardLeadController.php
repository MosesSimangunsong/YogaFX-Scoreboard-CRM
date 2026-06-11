<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use App\Services\Participants\IssueParticipantAccessLink;
use App\Services\Participants\UpsertParticipantFromLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScoreboardLeadController extends Controller
{
    public function show(string $slug): Response
    {
        $scoreboard = $this->findLiveScoreboard($slug);

        return Inertia::render('Public/Scoreboards/LeadForm', [
            'scoreboard' => [
                'id' => $scoreboard->id,
                'title' => $scoreboard->title,
                'slug' => $scoreboard->slug,
                'description' => $scoreboard->description,
                'thumbnail_url' => $scoreboard->thumbnail_url,
                'duration_minutes' => $scoreboard->duration_minutes,
                'show_progress_bar' => $scoreboard->show_progress_bar,
            ],
            'status' => session('status'),
        ]);
    }

    public function store(
        Request $request,
        string $slug,
        UpsertParticipantFromLead $upsertParticipant,
        IssueParticipantAccessLink $issueParticipantAccessLink,
    ): RedirectResponse {
        $scoreboard = $this->findLiveScoreboard($slug);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:120'],
        ]);

        $participant = $upsertParticipant->execute($validated, 'internal_form');
        $accessLink = $issueParticipantAccessLink->execute($scoreboard, $participant, 'internal_form');

        return redirect()
            ->route('public.scoreboards.lead.success', $scoreboard->slug)
            ->with('participant_first_name', $participant->first_name)
            ->with('access_code', $accessLink->access_code)
            ->with('access_url', $accessLink->access_url)
            ->with('status', 'lead-captured');
    }

    public function success(string $slug): Response
    {
        $scoreboard = $this->findLiveScoreboard($slug);

        return Inertia::render('Public/Scoreboards/LeadSuccess', [
            'scoreboard' => [
                'title' => $scoreboard->title,
                'slug' => $scoreboard->slug,
            ],
            'participantFirstName' => session('participant_first_name'),
            'accessLink' => session('access_code')
                ? [
                    'code' => session('access_code'),
                    'url' => session('access_url'),
                ]
                : null,
            'status' => session('status'),
        ]);
    }

    private function findLiveScoreboard(string $slug): Scoreboard
    {
        return Scoreboard::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('is_active', true)
            ->firstOrFail();
    }
}
