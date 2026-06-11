<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->json('tracking_payload')->nullable()->after('country');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->json('tracking_payload')->nullable()->after('score_payload');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('tracking_payload');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('tracking_payload');
        });
    }
};
