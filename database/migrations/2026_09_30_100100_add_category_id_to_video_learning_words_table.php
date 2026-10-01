<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('video_learning_words', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('id')->index();
        });

        // Ensure a default "General" category exists and assign all existing
        // (currently uncategorised) videos to it, so every record has a category.
        $generalId = DB::table('categories')->where('name', 'General')->value('id');

        if (!$generalId) {
            $generalId = DB::table('categories')->insertGetId([
                'name'        => 'General',
                'image_path'  => null,
                'is_active'   => true,
                'order_index' => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        DB::table('video_learning_words')
            ->whereNull('category_id')
            ->update(['category_id' => $generalId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_learning_words', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }
};
