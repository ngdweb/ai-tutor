<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite indexes aligned with the real query patterns so the
     * listings and APIs stay fast as the dataset grows.
     */
    public function up(): void
    {
        Schema::table('video_learning_words', function (Blueprint $table) {
            // category API: WHERE category_id = ? AND is_visible = 1 ORDER BY order_index
            $table->index(['category_id', 'is_visible', 'order_index'], 'vlw_cat_vis_order_idx');
            // flat API + admin list + "all videos": WHERE is_visible = 1 ORDER BY updated_at DESC
            $table->index(['is_visible', 'updated_at'], 'vlw_vis_updated_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            // categories API: WHERE is_active = 1 ORDER BY order_index
            $table->index(['is_active', 'order_index'], 'cat_active_order_idx');
        });
    }

    public function down(): void
    {
        Schema::table('video_learning_words', function (Blueprint $table) {
            $table->dropIndex('vlw_cat_vis_order_idx');
            $table->dropIndex('vlw_vis_updated_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('cat_active_order_idx');
        });
    }
};
