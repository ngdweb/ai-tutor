<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert stored full paths in categories and video_learning_words to only filenames.
     */
    public function up(): void
    {
        // Update categories: strip directory paths from image_path
        DB::table('categories')
            ->whereNotNull('image_path')
            ->get()
            ->each(function ($cat) {
                if ($cat->image_path && str_contains($cat->image_path, '/')) {
                    $fileName = basename(str_replace('\\', '/', $cat->image_path));
                    DB::table('categories')
                        ->where('id', $cat->id)
                        ->update(['image_path' => $fileName]);
                }
            });

        // Update video_learning_words: strip directory paths from video_path and thumbnail_path
        DB::table('video_learning_words')
            ->get()
            ->each(function ($video) {
                $updates = [];

                if ($video->video_path && str_contains($video->video_path, '/')) {
                    $updates['video_path'] = basename(str_replace('\\', '/', $video->video_path));
                }

                if ($video->thumbnail_path && str_contains($video->thumbnail_path, '/')) {
                    $updates['thumbnail_path'] = basename(str_replace('\\', '/', $video->thumbnail_path));
                }

                if (!empty($updates)) {
                    DB::table('video_learning_words')
                        ->where('id', $video->id)
                        ->update($updates);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Path reversal is not required as filenames are resolved dynamically.
    }
};
