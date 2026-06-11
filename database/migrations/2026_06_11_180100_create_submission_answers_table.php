<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scoreboard_question_id')->constrained('scoreboard_questions')->cascadeOnDelete();
            $table->foreignId('scoreboard_question_option_id')->nullable()->constrained('scoreboard_question_options')->nullOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->text('answer_text')->nullable();
            $table->decimal('answer_number', 10, 2)->nullable();
            $table->json('answer_payload')->nullable();
            $table->json('question_snapshot')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'scoreboard_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_answers');
    }
};
