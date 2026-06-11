<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantAccessLink;
use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\ScoreboardQuestionOption;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantQuizFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_route_creates_or_resumes_in_progress_submission(): void
    {
        [$accessLink] = $this->createAssessmentContext();

        $response = $this->get(route('public.scoreboards.assessment.show', $accessLink->access_code));

        $response->assertOk();

        $this->assertDatabaseHas('submissions', [
            'participant_access_link_id' => $accessLink->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_participant_can_save_answer_and_continue_to_next_question(): void
    {
        [$accessLink, $questions] = $this->createAssessmentContext();
        $firstQuestion = $questions[0];
        $nextQuestion = $questions[1];
        $selectedOption = $firstQuestion->options()->orderBy('sort_order')->firstOrFail();

        $response = $this->post(route('public.scoreboards.assessment.store', $accessLink->access_code), [
            'question_id' => $firstQuestion->id,
            'selected_option_ids' => [$selectedOption->id],
        ]);

        $response->assertRedirect(route('public.scoreboards.assessment.show', [
            'accessCode' => $accessLink->access_code,
            'question' => $nextQuestion->id,
        ], absolute: false));

        $submission = Submission::query()->where('participant_access_link_id', $accessLink->id)->firstOrFail();

        $this->assertDatabaseHas('submission_answers', [
            'submission_id' => $submission->id,
            'scoreboard_question_id' => $firstQuestion->id,
            'scoreboard_question_option_id' => $selectedOption->id,
        ]);
    }

    public function test_final_question_submission_marks_submission_as_submitted(): void
    {
        [$accessLink, $questions] = $this->createAssessmentContext();

        $firstOption = $questions[0]->options()->orderBy('sort_order')->firstOrFail();

        $this->post(route('public.scoreboards.assessment.store', $accessLink->access_code), [
            'question_id' => $questions[0]->id,
            'selected_option_ids' => [$firstOption->id],
        ]);

        $response = $this->post(route('public.scoreboards.assessment.store', $accessLink->access_code), [
            'question_id' => $questions[1]->id,
            'answer_text' => 'I am ready to continue with the next program.',
        ]);

        $response->assertRedirect(route('public.scoreboards.completed', $accessLink->access_code, absolute: false));

        $this->assertDatabaseHas('submissions', [
            'participant_access_link_id' => $accessLink->id,
            'status' => 'submitted',
        ]);
    }

    public function test_required_question_cannot_be_submitted_without_answer(): void
    {
        [$accessLink, $questions] = $this->createAssessmentContext();

        $response = $this->from(route('public.scoreboards.assessment.show', $accessLink->access_code))
            ->post(route('public.scoreboards.assessment.store', $accessLink->access_code), [
                'question_id' => $questions[0]->id,
                'selected_option_ids' => [],
            ]);

        $response->assertSessionHasErrors('selected_option_ids');
    }

    private function createAssessmentContext(): array
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Readiness Assessment',
            'slug' => 'readiness-assessment',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $participant = Participant::create([
            'first_name' => 'Participant',
            'last_name' => 'Quiz',
            'email' => 'participant-quiz@example.com',
            'whatsapp' => '6200099999',
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

        $firstQuestion = ScoreboardQuestion::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'Choose one option',
            'question_text' => 'Which path fits you best right now?',
            'question_type' => 'radio_buttons',
            'sort_order' => 1,
            'required' => true,
        ]);

        ScoreboardQuestionOption::create([
            'question_id' => $firstQuestion->id,
            'label' => 'Option A',
            'internal_value' => 'option_a',
            'sort_order' => 1,
            'scoring_enabled' => true,
            'score_value' => 3,
        ]);

        ScoreboardQuestionOption::create([
            'question_id' => $firstQuestion->id,
            'label' => 'Option B',
            'internal_value' => 'option_b',
            'sort_order' => 2,
            'scoring_enabled' => true,
            'score_value' => 5,
        ]);

        $secondQuestion = ScoreboardQuestion::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'Tell us more',
            'question_text' => 'What is the main goal you want help with?',
            'question_type' => 'open_text',
            'sort_order' => 2,
            'required' => true,
            'character_limit' => 500,
        ]);

        return [$accessLink, collect([$firstQuestion, $secondQuestion])];
    }
}
