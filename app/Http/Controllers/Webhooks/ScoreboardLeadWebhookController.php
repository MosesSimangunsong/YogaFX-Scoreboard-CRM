<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use App\Services\Participants\IssueParticipantAccessLink;
use App\Services\Participants\UpsertParticipantFromLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreboardLeadWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        string $slug,
        UpsertParticipantFromLead $upsertParticipant,
        IssueParticipantAccessLink $issueParticipantAccessLink,
    ): JsonResponse {
        $scoreboard = Scoreboard::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:120'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:255'],
            'landing_page' => ['nullable', 'string', 'max:255'],
        ]);

        $participant = $upsertParticipant->execute($validated, 'webhook');
        $accessLink = $issueParticipantAccessLink->execute($scoreboard, $participant, 'webhook');

        return response()->json([
            'status' => 'accepted',
            'scoreboard' => [
                'id' => $scoreboard->id,
                'slug' => $scoreboard->slug,
                'title' => $scoreboard->title,
            ],
            'participant' => [
                'id' => $participant->id,
                'first_name' => $participant->first_name,
                'last_name' => $participant->last_name,
                'email' => $participant->email,
                'whatsapp' => $participant->whatsapp,
            ],
            'access_link' => [
                'code' => $accessLink->access_code,
                'url' => $accessLink->access_url,
                'status' => $accessLink->status,
            ],
            'message' => 'Lead has been captured and a unique access link is ready.',
        ], 202);
    }
}
