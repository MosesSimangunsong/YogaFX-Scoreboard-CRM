<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoreboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('scoring_mode')->default('points');
            $table->string('result_mode')->default('score_or_range');
            $table->boolean('is_active')->default(false);
            $table->boolean('show_progress_bar')->default(true);
            $table->boolean('allow_back_navigation')->default(true);
            $table->string('logo_path')->nullable();
            $table->string('logo_max_width')->nullable();
            $table->string('logo_alignment')->nullable();
            $table->string('logo_link')->nullable();
            $table->string('header_position')->nullable();
            $table->text('section_background')->nullable();
            $table->unsignedInteger('top_margin')->nullable();
            $table->unsignedInteger('bottom_margin')->nullable();
            $table->text('footer_content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoreboards');
    }
};
