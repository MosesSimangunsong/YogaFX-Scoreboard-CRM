<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoreboard_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('scoreboard_questions')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('internal_value')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_correct')->default(false);
            $table->boolean('scoring_enabled')->default(false);
            $table->decimal('score_value', 10, 2)->nullable();
            $table->boolean('jump_enabled')->default(false);
            $table->foreignId('jump_to_question_id')->nullable()->constrained('scoreboard_questions')->nullOnDelete();
            $table->boolean('is_other_option')->default(false);
            $table->boolean('is_fixed_option')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoreboard_question_options');
    }
};
