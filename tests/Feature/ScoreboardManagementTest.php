<?php

namespace Tests\Feature;

use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\ScoreboardQuestionOption;
use App\Models\ScoreboardResultRange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreboardManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_scoreboards_index_page_is_displayed_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.scoreboards.index'));

        $response->assertOk();
    }

    public function test_scoreboard_can_be_created_and_redirects_to_builder(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.scoreboards.store'), [
                'title' => 'Teacher Potential Scoreboard',
                'slug' => 'teacher-potential-scoreboard',
                'description' => 'Assessment shell for teacher potential.',
                'status' => 'draft',
                'duration_minutes' => 15,
                'scoring_mode' => 'points',
                'result_mode' => 'score_or_range',
                'is_active' => false,
                'show_progress_bar' => true,
                'allow_back_navigation' => true,
            ]);

        $scoreboard = Scoreboard::first();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.scoreboards.builder', $scoreboard, absolute: false));

        $this->assertNotNull($scoreboard);
        $this->assertSame('Teacher Potential Scoreboard', $scoreboard->title);
        $this->assertSame($user->id, $scoreboard->created_by_user_id);
    }

    public function test_question_can_be_added_to_scoreboard_builder_flow(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Mobility Assessment',
            'slug' => 'mobility-assessment',
            'status' => 'draft',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => false,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('admin.scoreboards.questions.store', $scoreboard));

        $scoreboard->refresh();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.scoreboards.builder', [
                'assessment' => $scoreboard,
                'question' => $scoreboard->questions()->first(),
            ], absolute: false));

        $this->assertSame(1, $scoreboard->questions()->count());
    }

    public function test_scoreboard_cannot_be_created_as_published_before_builder_configuration(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.scoreboards.create'))
            ->post(route('admin.scoreboards.store'), [
                'title' => 'Too Early Publish',
                'slug' => 'too-early-publish',
                'description' => 'Trying to publish too early.',
                'status' => 'published',
                'duration_minutes' => 15,
                'scoring_mode' => 'points',
                'result_mode' => 'score_or_range',
                'is_active' => true,
                'show_progress_bar' => true,
                'allow_back_navigation' => true,
            ]);

        $response
            ->assertRedirect(route('admin.scoreboards.create', absolute: false))
            ->assertSessionHasErrors(['status']);
    }

    public function test_scoreboard_cannot_be_published_without_questions(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Unready Publish',
            'slug' => 'unready-publish',
            'status' => 'draft',
            'scoring_mode' => 'points',
            'result_mode' => 'score_or_range',
            'is_active' => false,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('admin.scoreboards.edit', $scoreboard))
            ->put(route('admin.scoreboards.update', $scoreboard), [
                'title' => $scoreboard->title,
                'slug' => $scoreboard->slug,
                'description' => '',
                'status' => 'published',
                'duration_minutes' => 15,
                'scoring_mode' => 'points',
                'result_mode' => 'score_or_range',
                'is_active' => true,
                'show_progress_bar' => true,
                'allow_back_navigation' => true,
            ]);

        $response
            ->assertRedirect(route('admin.scoreboards.edit', $scoreboard, absolute: false))
            ->assertSessionHasErrors(['status']);
    }

    public function test_range_only_scoreboard_requires_result_ranges_before_publishing(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Range Only',
            'slug' => 'range-only',
            'status' => 'draft',
            'scoring_mode' => 'points',
            'result_mode' => 'range_only',
            'is_active' => false,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $question = ScoreboardQuestion::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'Readiness',
            'question_text' => 'Pick one',
            'question_type' => 'radio_buttons',
            'sort_order' => 1,
            'required' => true,
        ]);

        ScoreboardQuestionOption::create([
            'question_id' => $question->id,
            'label' => 'Yes',
            'internal_value' => 'yes',
            'sort_order' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('admin.scoreboards.edit', $scoreboard))
            ->put(route('admin.scoreboards.update', $scoreboard), [
                'title' => $scoreboard->title,
                'slug' => $scoreboard->slug,
                'description' => '',
                'status' => 'published',
                'duration_minutes' => 15,
                'scoring_mode' => 'points',
                'result_mode' => 'range_only',
                'is_active' => true,
                'show_progress_bar' => true,
                'allow_back_navigation' => true,
            ]);

        $response
            ->assertRedirect(route('admin.scoreboards.edit', $scoreboard, absolute: false))
            ->assertSessionHasErrors(['result_mode']);
    }

    public function test_ready_scoreboard_can_be_published(): void
    {
        $user = User::factory()->create();
        $scoreboard = Scoreboard::create([
            'created_by_user_id' => $user->id,
            'title' => 'Ready Scoreboard',
            'slug' => 'ready-scoreboard',
            'status' => 'draft',
            'scoring_mode' => 'points',
            'result_mode' => 'range_only',
            'is_active' => false,
            'show_progress_bar' => true,
            'allow_back_navigation' => true,
        ]);

        $question = ScoreboardQuestion::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'Readiness',
            'question_text' => 'Pick one',
            'question_type' => 'radio_buttons',
            'sort_order' => 1,
            'required' => true,
        ]);

        ScoreboardQuestionOption::create([
            'question_id' => $question->id,
            'label' => 'Yes',
            'internal_value' => 'yes',
            'sort_order' => 1,
        ]);

        ScoreboardResultRange::create([
            'scoreboard_id' => $scoreboard->id,
            'title' => 'Qualified',
            'min_score' => 0,
            'max_score' => 10,
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.scoreboards.update', $scoreboard), [
                'title' => $scoreboard->title,
                'slug' => $scoreboard->slug,
                'description' => '',
                'status' => 'published',
                'duration_minutes' => 15,
                'scoring_mode' => 'points',
                'result_mode' => 'range_only',
                'is_active' => true,
                'show_progress_bar' => true,
                'allow_back_navigation' => true,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.scoreboards.edit', $scoreboard, absolute: false));

        $this->assertDatabaseHas('scoreboards', [
            'id' => $scoreboard->id,
            'status' => 'published',
            'is_active' => true,
        ]);
    }
}
