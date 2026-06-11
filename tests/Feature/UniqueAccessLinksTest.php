<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantAccessLink;
use App\Models\Scoreboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueAccessLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_lead_flow_creates_unique_access_link(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Assessment Access',
            'slug' => 'assessment-access',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $response = $this->post(route('public.scoreboards.lead.store', $scoreboard->slug), [
            'first_name' => 'Access',
            'last_name' => 'Tester',
            'email' => 'access@example.com',
            'whatsapp' => '081111111111',
            'country' => 'Indonesia',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('public.scoreboards.lead.success', $scoreboard->slug, absolute: false));

        $participant = Participant::query()->where('email', 'access@example.com')->firstOrFail();
        $accessLink = ParticipantAccessLink::query()
            ->where('scoreboard_id', $scoreboard->id)
            ->where('participant_id', $participant->id)
            ->first();

        $this->assertNotNull($accessLink);
        $this->assertSame('active', $accessLink->status);
        $this->assertSame(40, strlen($accessLink->access_code));
    }

    public function test_webhook_returns_existing_active_access_link_for_same_participant_and_scoreboard(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Webhook Access',
            'slug' => 'webhook-access',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $firstResponse = $this->postJson(route('webhooks.scoreboards.lead', $scoreboard->slug), [
            'first_name' => 'Webhook',
            'last_name' => 'Tester',
            'email' => 'webhook-access@example.com',
            'whatsapp' => '628111111111',
            'country' => 'Indonesia',
        ]);

        $secondResponse = $this->postJson(route('webhooks.scoreboards.lead', $scoreboard->slug), [
            'first_name' => 'Webhook',
            'last_name' => 'Tester',
            'email' => 'webhook-access@example.com',
            'whatsapp' => '628111111111',
            'country' => 'Indonesia',
        ]);

        $firstCode = $firstResponse->json('access_link.code');
        $secondCode = $secondResponse->json('access_link.code');

        $this->assertSame($firstCode, $secondCode);
        $this->assertSame(1, ParticipantAccessLink::count());
    }

    public function test_access_route_resolves_participant_context_and_marks_first_access(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Live Access Route',
            'slug' => 'live-access-route',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $participant = Participant::create([
            'first_name' => 'Route',
            'last_name' => 'Tester',
            'email' => 'route@example.com',
            'whatsapp' => '6200011111',
            'source_type' => 'internal_form',
            'status' => 'active',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $accessLink = ParticipantAccessLink::create([
            'scoreboard_id' => $scoreboard->id,
            'participant_id' => $participant->id,
            'access_code' => str_repeat('a', 40),
            'status' => 'active',
            'source_type' => 'internal_form',
            'issued_at' => now(),
        ]);

        $response = $this->get(route('public.scoreboards.access', $accessLink->access_code));

        $response->assertOk();

        $accessLink->refresh();

        $this->assertNotNull($accessLink->first_accessed_at);
        $this->assertNotNull($accessLink->last_accessed_at);
    }

    public function test_invalid_access_route_returns_not_found(): void
    {
        $response = $this->get(route('public.scoreboards.access', 'missing-access-code'));

        $response->assertNotFound();
    }

    public function test_revoked_access_route_returns_not_found(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Revoked Access',
            'slug' => 'revoked-access',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $participant = Participant::create([
            'first_name' => 'Revoked',
            'last_name' => 'User',
            'email' => 'revoked@example.com',
            'whatsapp' => '6200044444',
            'source_type' => 'internal_form',
            'status' => 'active',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $accessLink = ParticipantAccessLink::create([
            'scoreboard_id' => $scoreboard->id,
            'participant_id' => $participant->id,
            'access_code' => str_repeat('b', 40),
            'status' => 'revoked',
            'source_type' => 'internal_form',
            'issued_at' => now(),
            'revoked_at' => now(),
        ]);

        $response = $this->get(route('public.scoreboards.access', $accessLink->access_code));

        $response->assertNotFound();
    }
}
