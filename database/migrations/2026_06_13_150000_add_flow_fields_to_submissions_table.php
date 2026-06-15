<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('current_question_id')
                ->nullable()
                ->after('completed_at')
                ->constrained('scoreboard_questions')
                ->nullOnDelete();
            $table->foreignId('last_answered_question_id')
                ->nullable()
                ->after('current_question_id')
                ->constrained('scoreboard_questions')
                ->nullOnDelete();
            $table->string('finished_reason', 80)
                ->nullable()
                ->after('last_answered_question_id');
            $table->json('progress_payload')
                ->nullable()
                ->after('tracking_payload');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_question_id');
            $table->dropConstrainedForeignId('last_answered_question_id');
            $table->dropColumn([
                'finished_reason',
                'progress_payload',
            ]);
        });
    }
};
