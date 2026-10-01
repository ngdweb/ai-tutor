<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('video_learning_words', function (Blueprint $table) {
            $table->unsignedInteger('episode_no')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_learning_words', function (Blueprint $table) {
            $table->dropColumn('episode_no');
        });
    }
};
