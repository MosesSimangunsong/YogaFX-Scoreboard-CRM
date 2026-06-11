<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_category_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('category_key');
            $table->decimal('score', 10, 2)->default(0);
            $table->unsignedInteger('answered_questions_count')->default(0);
            $table->timestamps();

            $table->unique(['submission_id', 'category_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_category_scores');
    }
};
