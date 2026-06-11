<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoreboard_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scoreboard_id')->constrained('scoreboards')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('question_text')->nullable();
            $table->string('question_type')->default('radio_buttons');
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('show_instruction')->default(false);
            $table->text('instruction_text')->nullable();
            $table->boolean('required')->default(true);
            $table->boolean('randomize_answers_order')->default(false);
            $table->boolean('jump_enabled')->default(false);
            $table->foreignId('jump_to_question_id')->nullable()->constrained('scoreboard_questions')->nullOnDelete();
            $table->boolean('show_maybe_answer')->default(true);
            $table->boolean('allow_multi_select')->default(false);
            $table->unsignedInteger('min_count')->nullable();
            $table->unsignedInteger('max_count')->nullable();
            $table->boolean('allow_other_option')->default(false);
            $table->boolean('show_labels')->default(true);
            $table->decimal('score_range_min', 10, 2)->nullable();
            $table->decimal('score_range_max', 10, 2)->nullable();
            $table->decimal('starting_score', 10, 2)->nullable();
            $table->unsignedInteger('section_count')->nullable();
            $table->boolean('allow_decimals')->default(false);
            $table->string('input_type')->nullable();
            $table->unsignedInteger('character_limit')->nullable();
            $table->boolean('show_score_tooltip')->default(false);
            $table->string('score_tooltip_format')->nullable();
            $table->string('answer_image_fit')->nullable();
            $table->unsignedInteger('answers_per_row')->nullable();
            $table->string('scoring_category')->nullable();
            $table->string('left_label')->nullable();
            $table->string('center_label')->nullable();
            $table->string('right_label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoreboard_questions');
    }
};
