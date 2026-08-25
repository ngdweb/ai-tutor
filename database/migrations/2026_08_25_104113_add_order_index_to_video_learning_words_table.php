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
            $table->integer('order_index')->default(0)->after('is_visible')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_learning_words', function (Blueprint $table) {
            $table->dropColumn('order_index');
        });
    }
};
