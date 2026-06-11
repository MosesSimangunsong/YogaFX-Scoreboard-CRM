<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoreboard_result_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scoreboard_id')->constrained('scoreboards')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('recommendation')->nullable();
            $table->decimal('min_score', 10, 2)->nullable();
            $table->decimal('max_score', 10, 2)->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoreboard_result_ranges');
    }
};
