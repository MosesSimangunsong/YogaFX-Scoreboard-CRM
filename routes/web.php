<?php

use App\Http\Controllers\Admin\ScoreboardController;
use App\Http\Controllers\Admin\ScoreboardDesignController;
use App\Http\Controllers\Admin\ScoreboardOptionController;
use App\Http\Controllers\Admin\ScoreboardQuestionController;
use App\Http\Controllers\Admin\ScoreboardResultRangeController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ScoreboardAccessController;
use App\Http\Controllers\Public\ScoreboardAssessmentController;
use App\Http\Controllers\Public\ScoreboardLeadController;
use App\Http\Controllers\Webhooks\ScoreboardLeadWebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $featuredScoreboard = \App\Models\Scoreboard::query()
        ->publiclyAvailable()
        ->latest('updated_at')
        ->first();

    return Inertia::render('Public/Home', [
        'canLogin' => Route::has('login'),
        'featuredScoreboard' => $featuredScoreboard
            ? [
                'title' => $featuredScoreboard->title,
                'slug' => $featuredScoreboard->slug,
            ]
            : null,
    ]);
})->name('public.home');

Route::get('/scoreboards/{slug}', [ScoreboardLeadController::class, 'show'])
    ->name('public.scoreboards.lead.show');
Route::post('/scoreboards/{slug}/participants', [ScoreboardLeadController::class, 'store'])
    ->name('public.scoreboards.lead.store');
Route::get('/scoreboards/{slug}/lead-captured', [ScoreboardLeadController::class, 'success'])
    ->name('public.scoreboards.lead.success');
Route::get('/s/{accessCode}', ScoreboardAccessController::class)
    ->name('public.scoreboards.access');
Route::get('/s/{accessCode}/assessment', [ScoreboardAssessmentController::class, 'show'])
    ->name('public.scoreboards.assessment.show');
Route::post('/s/{accessCode}/assessment', [ScoreboardAssessmentController::class, 'store'])
    ->name('public.scoreboards.assessment.store');
Route::get('/s/{accessCode}/completed', [ScoreboardAssessmentController::class, 'completed'])
    ->name('public.scoreboards.completed');
Route::post('/webhooks/scoreboards/{slug}/lead', ScoreboardLeadWebhookController::class)
    ->name('webhooks.scoreboards.lead');

Route::get('/admin', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('scoreboards', ScoreboardController::class)
            ->except(['show'])
            ->scoped();
        Route::get('scoreboards/{assessment}/builder/{question?}', [ScoreboardController::class, 'builder'])
            ->name('scoreboards.builder');
        Route::post('scoreboards/{assessment}/questions', [ScoreboardQuestionController::class, 'store'])
            ->name('scoreboards.questions.store');
        Route::patch('scoreboards/{assessment}/questions/{question}', [ScoreboardQuestionController::class, 'update'])
            ->name('scoreboards.questions.update');
        Route::delete('scoreboards/{assessment}/questions/{question}', [ScoreboardQuestionController::class, 'destroy'])
            ->name('scoreboards.questions.destroy');
        Route::post('scoreboards/{assessment}/questions/{question}/options', [ScoreboardOptionController::class, 'store'])
            ->name('scoreboards.options.store');
        Route::patch('scoreboards/{assessment}/questions/{question}/options/{option}', [ScoreboardOptionController::class, 'update'])
            ->name('scoreboards.options.update');
        Route::delete('scoreboards/{assessment}/questions/{question}/options/{option}', [ScoreboardOptionController::class, 'destroy'])
            ->name('scoreboards.options.destroy');
        Route::post('scoreboards/{assessment}/result-ranges', [ScoreboardResultRangeController::class, 'store'])
            ->name('scoreboards.result-ranges.store');
        Route::patch('scoreboards/{assessment}/result-ranges/{resultRange}', [ScoreboardResultRangeController::class, 'update'])
            ->name('scoreboards.result-ranges.update');
        Route::delete('scoreboards/{assessment}/result-ranges/{resultRange}', [ScoreboardResultRangeController::class, 'destroy'])
            ->name('scoreboards.result-ranges.destroy');
        Route::patch('scoreboards/{scoreboard}/design', [ScoreboardDesignController::class, 'update'])
            ->name('scoreboards.design.update');
        Route::get('submissions', [SubmissionController::class, 'index'])
            ->name('submissions.index');
        Route::get('submissions/{submission}', [SubmissionController::class, 'show'])
            ->name('submissions.show');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
