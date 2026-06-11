<?php

namespace Tests\Feature;

use App\Mail\SubmissionResultMail;
use App\Models\Participant;
use App\Models\ParticipantAccessLink;
use App\Models\PdfReport;
use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\ScoreboardQuestionOption;
use App\Models\ScoreboardResultRange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfAndEmailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_submission_generates_pdf_report_and_sends_email(): void
    {
        Storage::fake('local');
        Mail::fake();

        [$accessLink, $question, $optionId] = $this->createDeliveryContext();

        $response = $this->post(route('public.scoreboards.assessment.store', $accessLink->access_code), [
            'question_id' => $question->id,
            'selected_option_ids' => [$optionId],
        ]);

        $response->assertRedirect(route('public.scoreboards.completed', $accessLink->access_code, absolute: false));

        $this->assertDatabaseHas('pdf_reports', [
            'status' => 'generated',
        ]);

        $this->assertDatabaseHas('email_logs', [
            'status' => 'sent',
            'recipient_email' => 'delivery@example.com',
        ]);

        $report = PdfReport::query()->firstOrFail();

        Storage::disk('local')->assertExists($report->path);

        Mail::assertSent(SubmissionResultMail::class, function (SubmissionResultMail $mail) {
            return $mail->submission->participant->email === 'delivery@example.com';
        });
    }

    private function createDeliveryContext(): array
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Delivery Assessment',
            'slug' => 'delivery-assessment',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        ScoreboardResultRange::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'Qualified',
            'description' => 'This lead is qualified.',
            'recommendation' => 'Send the premium next step.',
            'min_score' => 5,
            'max_score' => 10,
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $participant = Participant::create([
            'first_name' => 'Delivery',
            'last_name' => 'User',
            'email' => 'delivery@example.com',
            'whatsapp' => '6200088888',
            'source_type' => 'internal_form',
            'status' => 'active',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $accessLink = ParticipantAccessLink::create([
            'scoreboard_id' => $scoreboard->id,
            'participant_id' => $participant->id,
            'access_code' => bin2hex(random_bytes(20)),
            'status' => 'active',
            'source_type' => 'internal_form',
            'issued_at' => now(),
        ]);

        $question = ScoreboardQuestion::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'Qualification',
            'question_text' => 'Select your path.',
            'question_type' => 'radio_buttons',
            'sort_order' => 1,
            'required' => true,
            'scoring_category' => 'qualification',
        ]);

        $option = ScoreboardQuestionOption::create([
            'question_id' => $question->id,
            'label' => 'Qualified',
            'internal_value' => 'qualified',
            'sort_order' => 1,
            'scoring_enabled' => true,
            'score_value' => 6,
        ]);

        return [$accessLink, $question, $option->id];
    }
}
