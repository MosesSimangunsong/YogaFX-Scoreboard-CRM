<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantAccessLink;
use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\ScoreboardQuestionOption;
use App\Models\ScoreboardResultRange;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoringAndResultEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_is_scored_and_result_range_is_assigned_after_final_submit(): void
    {
        [$accessLink, $questions] = $this->createScoredAssessmentContext();

        $this->post(route('public.scoreboards.assessment.store', $accessLink->access_code), [
            'question_id' => $questions[0]->id,
            'selected_option_ids' => [$questions[0]->options()->where('internal_value', 'strength')->value('id')],
        ]);

        $response = $this->post(route('public.scoreboards.assessment.store', $accessLink->access_code), [
            'question_id' => $questions[1]->id,
            'answer_number' => 8,
        ]);

        $response->assertRedirect(route('public.scoreboards.completed', $accessLink->access_code, absolute: false));

        $submission = Submission::query()
            ->with(['categoryScores', 'resultRange'])
            ->where('participant_access_link_id', $accessLink->id)
            ->firstOrFail();

        $this->assertSame('submitted', $submission->status);
        $this->assertSame('13.00', number_format((float) $submission->overall_score, 2, '.', ''));
        $this->assertNotNull($submission->scored_at);
        $this->assertSame('High Readiness', $submission->result_title);
        $this->assertSame('mindset', $submission->categoryScores->first()->category_key);
        $this->assertSame('13.00', number_format((float) $submission->categoryScores->first()->score, 2, '.', ''));
    }

    public function test_result_page_is_displayed_for_submitted_submission(): void
    {
        [$accessLink, $questions] = $this->createScoredAssessmentContext();

        $this->post(route('public.scoreboards.assessment.store', $accessLink->access_code), [
            'question_id' => $questions[0]->id,
            'selected_option_ids' => [$questions[0]->options()->where('internal_value', 'strength')->value('id')],
        ]);

        $this->post(route('public.scoreboards.assessment.store', $accessLink->access_code), [
            'question_id' => $questions[1]->id,
            'answer_number' => 8,
        ]);

        $response = $this->get(route('public.scoreboards.completed', $accessLink->access_code));

        $response->assertOk();
    }

    private function createScoredAssessmentContext(): array
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Scored Readiness',
            'slug' => 'scored-readiness',
            'status' => 'published',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => true,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        ScoreboardResultRange::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'High Readiness',
            'description' => 'You show strong readiness signals.',
            'recommendation' => 'Proceed to the premium follow-up path.',
            'min_score' => 10,
            'max_score' => 20,
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $participant = Participant::create([
            'first_name' => 'Scored',
            'last_name' => 'Participant',
            'email' => 'scored@example.com',
            'whatsapp' => '6200012121',
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
            'title' => 'Mindset Fit',
            'question_text' => 'Choose the best fit.',
            'question_type' => 'radio_buttons',
            'sort_order' => 1,
            'required' => true,
            'scoring_category' => 'mindset',
        ]);

        ScoreboardQuestionOption::create([
            'question_id' => $firstQuestion->id,
            'label' => 'Strength',
            'internal_value' => 'strength',
            'sort_order' => 1,
            'scoring_enabled' => true,
            'score_value' => 5,
        ]);

        ScoreboardQuestionOption::create([
            'question_id' => $firstQuestion->id,
            'label' => 'Starter',
            'internal_value' => 'starter',
            'sort_order' => 2,
            'scoring_enabled' => true,
            'score_value' => 2,
        ]);

        $secondQuestion = ScoreboardQuestion::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'Self Rating',
            'question_text' => 'Rate yourself from 0-10.',
            'question_type' => 'numeric',
            'sort_order' => 2,
            'required' => true,
            'allow_decimals' => false,
            'score_range_min' => 0,
            'score_range_max' => 10,
            'scoring_category' => 'mindset',
        ]);

        return [$accessLink, collect([$firstQuestion, $secondQuestion])];
    }
}
