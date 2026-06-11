<?php

namespace App\Services\Participants;

use App\Models\Participant;
use App\Models\ParticipantAccessLink;
use App\Models\Scoreboard;

class IssueParticipantAccessLink
{
    public function execute(Scoreboard $scoreboard, Participant $participant, string $sourceType): ParticipantAccessLink
    {
        $existingLink = ParticipantAccessLink::query()
            ->where('scoreboard_id', $scoreboard->id)
            ->where('participant_id', $participant->id)
            ->resolvable()
            ->latest('issued_at')
            ->first();

        if ($existingLink) {
            return $existingLink;
        }

        return ParticipantAccessLink::create([
            'scoreboard_id' => $scoreboard->id,
            'participant_id' => $participant->id,
            'access_code' => $this->generateUniqueAccessCode(),
            'status' => 'active',
            'source_type' => $sourceType,
            'issued_at' => now(),
        ]);
    }

    private function generateUniqueAccessCode(): string
    {
        do {
            $candidate = bin2hex(random_bytes(20));
        } while (ParticipantAccessLink::query()->where('access_code', $candidate)->exists());

        return $candidate;
    }
}
