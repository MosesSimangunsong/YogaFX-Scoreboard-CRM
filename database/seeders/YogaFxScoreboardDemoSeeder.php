<?php

namespace Database\Seeders;

use App\Models\EmailLog;
use App\Models\Participant;
use App\Models\ParticipantAccessLink;
use App\Models\PdfReport;
use App\Models\Scoreboard;
use App\Models\ScoreboardQuestion;
use App\Models\ScoreboardQuestionOption;
use App\Models\ScoreboardResultRange;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use App\Models\SubmissionCategoryScore;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class YogaFxScoreboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@yogafx-scoreboard.local'],
            [
                'name' => 'YogaFX Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $scoreboard = Scoreboard::query()->updateOrCreate(
            ['slug' => 'yogafx-readiness-scoreboard'],
            [
                'created_by_user_id' => $admin->id,
                'title' => 'YogaFX Readiness Scoreboard',
                'description' => 'Demo production-style scoreboard for lead qualification, assessment, and follow-up QA.',
                'status' => 'published',
                'duration_minutes' => 8,
                'scoring_mode' => 'points',
                'result_mode' => 'score_or_range',
                'is_active' => true,
                'show_progress_bar' => true,
                'allow_back_navigation' => true,
            ],
        );

        if ($scoreboard->questions()->count() === 0) {
            $mindsetQuestion = ScoreboardQuestion::create([
                'scoreboard_id' => $scoreboard->id,
                'title' => 'Current Mindset',
                'question_text' => 'Which statement best reflects your current readiness?',
                'question_type' => 'radio_buttons',
                'sort_order' => 1,
                'required' => true,
                'scoring_category' => 'readiness',
            ]);

            ScoreboardQuestionOption::create([
                'question_id' => $mindsetQuestion->id,
                'label' => 'I am ready to commit right away.',
                'internal_value' => 'ready_now',
                'sort_order' => 1,
                'scoring_enabled' => true,
                'score_value' => 5,
            ]);

            ScoreboardQuestionOption::create([
                'question_id' => $mindsetQuestion->id,
                'label' => 'I am exploring, but still serious.',
                'internal_value' => 'exploring',
                'sort_order' => 2,
                'scoring_enabled' => true,
                'score_value' => 3,
            ]);

            ScoreboardQuestionOption::create([
                'question_id' => $mindsetQuestion->id,
                'label' => 'I need much more time before deciding.',
                'internal_value' => 'not_ready',
                'sort_order' => 3,
                'scoring_enabled' => true,
                'score_value' => 1,
            ]);

            ScoreboardQuestion::create([
                'scoreboard_id' => $scoreboard->id,
                'title' => 'Self Rating',
                'question_text' => 'On a scale from 0-10, how ready are you to start now?',
                'question_type' => 'numeric',
                'sort_order' => 2,
                'required' => true,
                'allow_decimals' => false,
                'score_range_min' => 0,
                'score_range_max' => 10,
                'scoring_category' => 'readiness',
            ]);

            ScoreboardQuestion::create([
                'scoreboard_id' => $scoreboard->id,
                'title' => 'Main Goal',
                'question_text' => 'What is the biggest outcome you want from YogaFX right now?',
                'question_type' => 'open_text',
                'sort_order' => 3,
                'required' => true,
                'character_limit' => 500,
            ]);
        }

        if ($scoreboard->resultRanges()->count() === 0) {
            ScoreboardResultRange::create([
                'scoreboard_id' => $scoreboard->id,
                'title' => 'High Readiness',
                'description' => 'This participant shows strong conversion readiness.',
                'recommendation' => 'Prioritize premium follow-up and a direct offer.',
                'min_score' => 12,
                'max_score' => 15,
                'status' => 'active',
                'sort_order' => 1,
            ]);

            ScoreboardResultRange::create([
                'scoreboard_id' => $scoreboard->id,
                'title' => 'Moderate Readiness',
                'description' => 'This participant is engaged but may need nurturing.',
                'recommendation' => 'Use value-focused follow-up and educational messaging.',
                'min_score' => 7,
                'max_score' => 11.99,
                'status' => 'active',
                'sort_order' => 2,
            ]);

            ScoreboardResultRange::create([
                'scoreboard_id' => $scoreboard->id,
                'title' => 'Low Readiness',
                'description' => 'This participant is still early in the funnel.',
                'recommendation' => 'Keep the follow-up light and awareness-oriented.',
                'min_score' => 0,
                'max_score' => 6.99,
                'status' => 'active',
                'sort_order' => 3,
            ]);
        }

        $participant = Participant::query()->updateOrCreate(
            ['email' => 'participant.demo@yogafx.com'],
            [
                'first_name' => 'Alya',
                'last_name' => 'Demo',
                'whatsapp' => '628123450000',
                'country' => 'Indonesia',
                'tracking_payload' => [
                    'utm_source' => 'meta_ads',
                    'utm_campaign' => 'readiness-demo',
                    'utm_medium' => 'paid_social',
                    'landing_page' => '/scoreboards/yogafx-readiness-scoreboard',
                ],
                'source_type' => 'webhook',
                'status' => 'active',
                'first_seen_at' => now()->subDays(2),
                'last_seen_at' => now()->subHours(2),
            ],
        );

        $accessLink = ParticipantAccessLink::query()->updateOrCreate(
            [
                'scoreboard_id' => $scoreboard->id,
                'participant_id' => $participant->id,
            ],
            [
                'access_code' => 'yogafx-demo-access-0001',
                'status' => 'active',
                'source_type' => 'webhook',
                'issued_at' => now()->subHours(2),
                'first_accessed_at' => now()->subHours(2),
                'last_accessed_at' => now()->subHours(2),
            ],
        );

        $submission = Submission::query()->updateOrCreate(
            [
                'scoreboard_id' => $scoreboard->id,
                'participant_id' => $participant->id,
                'participant_access_link_id' => $accessLink->id,
            ],
            [
                'status' => 'submitted',
                'started_at' => now()->subHours(2),
                'submitted_at' => now()->subHours(2)->addMinutes(6),
                'completed_at' => now()->subHours(2)->addMinutes(6),
                'last_answered_at' => now()->subHours(2)->addMinutes(5),
                'overall_score' => 13,
                'score_payload' => [
                    'result_mode' => 'score_or_range',
                    'category_keys' => ['readiness'],
                    'answered_questions_count' => 3,
                ],
                'tracking_payload' => $participant->tracking_payload,
                'result_title' => 'High Readiness',
                'result_description' => 'This participant shows strong conversion readiness.',
                'result_recommendation' => 'Prioritize premium follow-up and a direct offer.',
                'scored_at' => now()->subHours(2)->addMinutes(6),
            ],
        );

        if ($submission->answers()->count() === 0) {
            $questions = $scoreboard->questions()->with('options')->orderBy('sort_order')->get();
            $firstQuestion = $questions->get(0);
            $secondQuestion = $questions->get(1);
            $thirdQuestion = $questions->get(2);
            $selectedOption = $firstQuestion?->options()->where('internal_value', 'ready_now')->first();

            if ($firstQuestion && $selectedOption) {
                SubmissionAnswer::create([
                    'submission_id' => $submission->id,
                    'scoreboard_question_id' => $firstQuestion->id,
                    'scoreboard_question_option_id' => $selectedOption->id,
                    'selected_option_ids' => [$selectedOption->id],
                    'answer_payload' => [
                        'question_type' => $firstQuestion->question_type,
                        'selected_options' => [[
                            'id' => $selectedOption->id,
                            'label' => $selectedOption->label,
                            'internal_value' => $selectedOption->internal_value,
                            'score_value' => $selectedOption->score_value,
                            'is_other_option' => false,
                        ]],
                    ],
                    'question_snapshot' => [
                        'id' => $firstQuestion->id,
                        'title' => $firstQuestion->title,
                        'question_text' => $firstQuestion->question_text,
                        'question_type' => $firstQuestion->question_type,
                        'required' => $firstQuestion->required,
                        'sort_order' => $firstQuestion->sort_order,
                        'scoring_category' => $firstQuestion->scoring_category,
                    ],
                    'answered_at' => now()->subHours(2)->addMinutes(2),
                ]);
            }

            if ($secondQuestion) {
                SubmissionAnswer::create([
                    'submission_id' => $submission->id,
                    'scoreboard_question_id' => $secondQuestion->id,
                    'answer_number' => 8,
                    'answer_payload' => [
                        'question_type' => $secondQuestion->question_type,
                        'selected_options' => [],
                    ],
                    'question_snapshot' => [
                        'id' => $secondQuestion->id,
                        'title' => $secondQuestion->title,
                        'question_text' => $secondQuestion->question_text,
                        'question_type' => $secondQuestion->question_type,
                        'required' => $secondQuestion->required,
                        'sort_order' => $secondQuestion->sort_order,
                        'scoring_category' => $secondQuestion->scoring_category,
                    ],
                    'answered_at' => now()->subHours(2)->addMinutes(4),
                ]);
            }

            if ($thirdQuestion) {
                SubmissionAnswer::create([
                    'submission_id' => $submission->id,
                    'scoreboard_question_id' => $thirdQuestion->id,
                    'answer_text' => 'I want a focused plan and premium support so I can start immediately.',
                    'answer_payload' => [
                        'question_type' => $thirdQuestion->question_type,
                        'selected_options' => [],
                    ],
                    'question_snapshot' => [
                        'id' => $thirdQuestion->id,
                        'title' => $thirdQuestion->title,
                        'question_text' => $thirdQuestion->question_text,
                        'question_type' => $thirdQuestion->question_type,
                        'required' => $thirdQuestion->required,
                        'sort_order' => $thirdQuestion->sort_order,
                        'scoring_category' => $thirdQuestion->scoring_category,
                    ],
                    'answered_at' => now()->subHours(2)->addMinutes(5),
                ]);
            }
        }

        SubmissionCategoryScore::query()->updateOrCreate(
            [
                'submission_id' => $submission->id,
                'category_key' => 'readiness',
            ],
            [
                'score' => 13,
                'answered_questions_count' => 2,
            ],
        );

        $pdfReport = PdfReport::query()->updateOrCreate(
            [
                'submission_id' => $submission->id,
                'file_name' => 'yogafx-readiness-submission-demo.pdf',
            ],
            [
                'disk' => 'local',
                'path' => 'reports/submissions/demo/yogafx-readiness-submission-demo.pdf',
                'mime_type' => 'application/pdf',
                'status' => 'generated',
                'generated_at' => now()->subHours(2)->addMinutes(7),
            ],
        );

        EmailLog::query()->updateOrCreate(
            [
                'submission_id' => $submission->id,
                'recipient_email' => $participant->email,
            ],
            [
                'pdf_report_id' => $pdfReport->id,
                'subject' => 'Your YogaFX Readiness Scoreboard result is ready',
                'mailer' => config('mail.default'),
                'status' => 'sent',
                'queued_at' => now()->subHours(2)->addMinutes(7),
                'sent_at' => now()->subHours(2)->addMinutes(8),
            ],
        );
    }
}
