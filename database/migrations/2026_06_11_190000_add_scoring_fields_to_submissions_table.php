<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->decimal('overall_score', 10, 2)->nullable()->after('last_answered_at');
            $table->json('score_payload')->nullable()->after('overall_score');
            $table->foreignId('scoreboard_result_range_id')
                ->nullable()
                ->after('score_payload')
                ->constrained('scoreboard_result_ranges')
                ->nullOnDelete();
            $table->string('result_title')->nullable()->after('scoreboard_result_range_id');
            $table->text('result_description')->nullable()->after('result_title');
            $table->text('result_recommendation')->nullable()->after('result_description');
            $table->timestamp('scored_at')->nullable()->after('result_recommendation');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scoreboard_result_range_id');
            $table->dropColumn([
                'overall_score',
                'score_payload',
                'result_title',
                'result_description',
                'result_recommendation',
                'scored_at',
            ]);
        });
    }
};
