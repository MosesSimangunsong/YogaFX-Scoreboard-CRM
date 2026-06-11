<?php

namespace App\Services\Participants;

use App\Models\Participant;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class UpsertParticipantFromLead
{
    public function execute(array $payload, string $sourceType, ?CarbonInterface $seenAt = null): Participant
    {
        $seenAt = $seenAt ? Carbon::instance($seenAt) : now();
        $trackingPayload = $this->extractTrackingPayload($payload);

        $participant = Participant::query()
            ->when(
                filled($payload['email'] ?? null),
                fn ($query) => $query->orWhere('email', $payload['email']),
            )
            ->when(
                filled($payload['whatsapp'] ?? null),
                fn ($query) => $query->orWhere('whatsapp', $payload['whatsapp']),
            )
            ->first();

        if (! $participant) {
            return Participant::create([
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'email' => $payload['email'] ?? null,
                'whatsapp' => $payload['whatsapp'] ?? null,
                'country' => $payload['country'] ?? null,
                'tracking_payload' => $trackingPayload ?: null,
                'source_type' => $sourceType,
                'status' => 'active',
                'first_seen_at' => $seenAt,
                'last_seen_at' => $seenAt,
            ]);
        }

        $participant->update([
            'first_name' => $payload['first_name'] ?: $participant->first_name,
            'last_name' => $payload['last_name'] ?: $participant->last_name,
            'email' => $payload['email'] ?? $participant->email,
            'whatsapp' => $payload['whatsapp'] ?? $participant->whatsapp,
            'country' => $payload['country'] ?? $participant->country,
            'tracking_payload' => $trackingPayload ?: $participant->tracking_payload,
            'last_seen_at' => $seenAt,
        ]);

        return $participant->refresh();
    }

    private function extractTrackingPayload(array $payload): array
    {
        return collect([
            'utm_source' => $payload['utm_source'] ?? null,
            'utm_campaign' => $payload['utm_campaign'] ?? null,
            'utm_medium' => $payload['utm_medium'] ?? null,
            'utm_term' => $payload['utm_term'] ?? null,
            'utm_content' => $payload['utm_content'] ?? null,
            'referrer' => $payload['referrer'] ?? null,
            'landing_page' => $payload['landing_page'] ?? null,
        ])->filter(fn ($value) => filled($value))->all();
    }
}
