<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learnup_words', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->unsignedInteger('episode_no')->nullable();
            $table->string('video_path');
            $table->string('video_name')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->longText('json_data');
            $table->boolean('is_visible')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            // Composite indexes matching the query patterns (same as the video module).
            $table->index(['category_id', 'is_visible', 'order_index'], 'lup_cat_vis_order_idx');
            $table->index(['is_visible', 'updated_at'], 'lup_vis_updated_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learnup_words');
    }
};
