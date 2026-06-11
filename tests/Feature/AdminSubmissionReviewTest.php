<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\Participant;
use App\Models\ParticipantAccessLink;
use App\Models\PdfReport;
use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use App\Models\SubmissionCategoryScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminSubmissionReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_submissions_index(): void
    {
        [$admin, $submission] = $this->createSubmissionReviewContext();

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.submissions.index'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Submissions/Index')
                ->has('submissions.data', 1)
                ->where('submissions.data.0.id', $submission->id)
                ->where('submissions.data.0.participant.email', 'review@example.com')
                ->where('submissions.data.0.delivery.email_status', 'sent')
            );
    }

    public function test_admin_can_view_submission_detail_with_answers_tracking_and_delivery(): void
    {
        [$admin, $submission] = $this->createSubmissionReviewContext();

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.submissions.show', $submission));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Submissions/Show')
                ->where('submission.id', $submission->id)
                ->where('submission.participant.email', 'review@example.com')
                ->where('submission.tracking_payload.utm_source', 'webhook')
                ->where('categoryScores.0.category_key', 'readiness')
                ->where('delivery.email_logs.0.status', 'sent')
                ->where('answers.0.answer_text', 'Participant wants premium guidance.')
            );
    }

    private function createSubmissionReviewContext(): array
    {
        $admin = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $admin->id,
            'title' => 'Review Scoreboard',
            'slug' => 'review-scoreboard',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $participant = Participant::create([
            'first_name' => 'Review',
            'last_name' => 'User',
            'email' => 'review@example.com',
            'whatsapp' => '6200012345',
            'country' => 'Indonesia',
            'tracking_payload' => [
                'utm_source' => 'webhook',
                'utm_campaign' => 'review-campaign',
            ],
            'source_type' => 'webhook',
            'status' => 'active',
            'first_seen_at' => now()->subHour(),
            'last_seen_at' => now(),
        ]);

        $accessLink = ParticipantAccessLink::create([
            'scoreboard_id' => $scoreboard->id,
            'participant_id' => $participant->id,
            'access_code' => bin2hex(random_bytes(20)),
            'status' => 'active',
            'source_type' => 'webhook',
            'issued_at' => now()->subHour(),
        ]);

        $submission = Submission::create([
            'scoreboard_id' => $scoreboard->id,
            'participant_id' => $participant->id,
            'participant_access_link_id' => $accessLink->id,
            'status' => 'submitted',
            'started_at' => now()->subMinutes(40),
            'submitted_at' => now()->subMinutes(20),
            'completed_at' => now()->subMinutes(20),
            'last_answered_at' => now()->subMinutes(21),
            'overall_score' => 9,
            'score_payload' => [
                'result_mode' => 'score_or_range',
            ],
            'tracking_payload' => [
                'utm_source' => 'webhook',
                'utm_campaign' => 'review-campaign',
            ],
            'result_title' => 'Qualified',
            'result_description' => 'Strong lead quality.',
            'result_recommendation' => 'Send premium follow-up.',
            'scored_at' => now()->subMinutes(19),
        ]);

        SubmissionCategoryScore::create([
            'submission_id' => $submission->id,
            'category_key' => 'readiness',
            'score' => 9,
            'answered_questions_count' => 1,
        ]);

        $question = ScoreboardQuestion::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'Main Goal',
            'question_text' => 'What kind of help do you need?',
            'question_type' => 'open_text',
            'sort_order' => 1,
            'required' => true,
            'scoring_category' => 'readiness',
        ]);

        SubmissionAnswer::create([
            'submission_id' => $submission->id,
            'scoreboard_question_id' => $question->id,
            'answer_text' => 'Participant wants premium guidance.',
            'answer_payload' => [
                'question_type' => 'open_text',
                'selected_options' => [],
            ],
            'question_snapshot' => [
                'title' => 'Main Goal',
                'question_text' => 'What kind of help do you need?',
                'question_type' => 'open_text',
                'sort_order' => 1,
                'scoring_category' => 'readiness',
            ],
            'answered_at' => now()->subMinutes(21),
        ]);

        $pdfReport = PdfReport::create([
            'submission_id' => $submission->id,
            'disk' => 'local',
            'path' => 'reports/review.pdf',
            'file_name' => 'review.pdf',
            'mime_type' => 'application/pdf',
            'status' => 'generated',
            'generated_at' => now()->subMinutes(18),
        ]);

        EmailLog::create([
            'submission_id' => $submission->id,
            'pdf_report_id' => $pdfReport->id,
            'recipient_email' => 'review@example.com',
            'subject' => 'Your result is ready',
            'mailer' => 'smtp',
            'status' => 'sent',
            'queued_at' => now()->subMinutes(18),
            'sent_at' => now()->subMinutes(17),
        ]);

        return [$admin, $submission];
    }
}
