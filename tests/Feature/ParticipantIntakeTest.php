<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\Scoreboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantIntakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_scoreboard_lead_form_is_displayed(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Teacher Readiness',
            'slug' => 'teacher-readiness',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $response = $this->get(route('public.scoreboards.lead.show', $scoreboard->slug));

        $response->assertOk();
    }

    public function test_internal_lead_form_creates_participant_record(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Mindset Assessment',
            'slug' => 'mindset-assessment',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $response = $this->post(route('public.scoreboards.lead.store', $scoreboard->slug), [
            'first_name' => 'Yoga',
            'last_name' => 'Tester',
            'email' => 'participant@example.com',
            'whatsapp' => '08123456789',
            'country' => 'Indonesia',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('public.scoreboards.lead.success', $scoreboard->slug, absolute: false));

        $this->assertDatabaseHas('participants', [
            'email' => 'participant@example.com',
            'source_type' => 'internal_form',
        ]);
    }

    public function test_webhook_lead_creates_participant_record(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Mobility Assessment',
            'slug' => 'mobility-assessment',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $response = $this->postJson(route('webhooks.scoreboards.lead', $scoreboard->slug), [
            'first_name' => 'Webhook',
            'last_name' => 'Lead',
            'email' => 'webhook@example.com',
            'whatsapp' => '628123456789',
            'country' => 'Indonesia',
            'utm_source' => 'meta',
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('status', 'accepted');

        $participant = Participant::query()->where('email', 'webhook@example.com')->firstOrFail();

        $this->assertDatabaseHas('participants', [
            'email' => 'webhook@example.com',
            'source_type' => 'webhook',
        ]);

        $this->assertSame('meta', data_get($participant->tracking_payload, 'utm_source'));
    }

    public function test_existing_participant_is_updated_instead_of_duplicated(): void
    {
        $participant = Participant::create([
            'first_name' => 'Existing',
            'last_name' => 'User',
            'email' => 'existing@example.com',
            'whatsapp' => '6200000000',
            'source_type' => 'internal_form',
            'status' => 'active',
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        $service = app(\App\Services\Participants\UpsertParticipantFromLead::class);

        $updated = $service->execute([
            'first_name' => 'Existing',
            'last_name' => 'Updated',
            'email' => 'existing@example.com',
            'whatsapp' => '6200000000',
            'country' => 'Indonesia',
        ], 'webhook');

        $this->assertSame($participant->id, $updated->id);
        $this->assertSame(1, Participant::count());
        $this->assertSame('Updated', $updated->last_name);
    }
}
