<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\LearnupWord;
use App\Models\VideoLearningWord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Mirror ALL data from the Video Learning module (categories + words + files)
 * into the LearnUp with AI module, 1:1 (same ids, same values). The LearnUp
 * target is cleared first so it becomes an exact copy. The Video module (source)
 * is never modified.
 */
class CopyVideoToLearnup extends Command
{
    protected $signature = 'learnup:copy-from-video {--force : Run without the confirmation prompt}';

    protected $description = 'Copy all Video Learning data (categories, words, files) into the LearnUp with AI module';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('This will REPLACE all LearnUp data with an exact copy of the Video module. Continue?')) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        // ── Clear target so it mirrors the source exactly (source untouched) ──
        DB::table('learnup_words')->delete();
        DB::table('learnup_categories')->delete();
        File::deleteDirectory(public_path('uploads/learnup_with_ai'));

        $catDone = 0; $catImg = 0; $catImgMiss = 0;
        $wordDone = 0; $vid = 0; $vidMiss = 0; $thumb = 0; $thumbMiss = 0;

        // ── Categories (same id, same values) ──
        foreach (Category::orderBy('id')->get() as $c) {
            DB::table('learnup_categories')->insert([
                'id'          => $c->id,
                'name'        => $c->name,
                'image_path'  => $c->image_path,   // raw stored basename
                'is_active'   => $c->is_active ? 1 : 0,
                'order_index' => (int) $c->order_index,
                'created_at'  => $c->created_at,
                'updated_at'  => $c->updated_at,
            ]);
            $catDone++;

            if ($c->image_path) {
                $src = $c->getImageRelativePath();
                $slug = Str::slug($c->name) ?: 'general';
                $destRel = 'uploads/learnup_with_ai/' . $slug . '/category/' . basename($c->image_path);
                if ($this->copyFile($src, $destRel)) { $catImg++; } else { $catImgMiss++; }
            }
        }

        // ── Words (same id, same category_id, same values) ──
        foreach (VideoLearningWord::orderBy('id')->get() as $v) {
            DB::table('learnup_words')->insert([
                'id'             => $v->id,
                'category_id'    => $v->category_id,
                'title'          => $v->title,
                'episode_no'     => $v->episode_no,
                'video_path'     => $v->video_path,         // raw stored basename
                'video_name'     => $v->video_name,
                'thumbnail_path' => $v->thumbnail_path,      // raw stored basename
                'json_data'      => $v->json_data,
                'is_visible'     => $v->is_visible ? 1 : 0,
                'order_index'    => (int) $v->order_index,
                'created_at'     => $v->created_at,
                'updated_at'     => $v->updated_at,
            ]);
            $wordDone++;

            $slug = Str::slug(optional(Category::find($v->category_id))->name ?? 'General') ?: 'general';

            if ($v->video_path) {
                $destRel = 'uploads/learnup_with_ai/' . $slug . '/videos/' . basename($v->video_path);
                if ($this->copyFile($v->getVideoRelativePath(), $destRel)) { $vid++; } else { $vidMiss++; }
            }
            if ($v->thumbnail_path) {
                $destRel = 'uploads/learnup_with_ai/' . $slug . '/thumbnails/' . basename($v->thumbnail_path);
                if ($this->copyFile($v->getThumbnailRelativePath(), $destRel)) { $thumb++; } else { $thumbMiss++; }
            }
        }

        LearnupWord::clearCategorySlugCache();

        $this->info("Categories copied: {$catDone} (images {$catImg}, missing-source {$catImgMiss})");
        $this->info("Words copied: {$wordDone} (videos {$vid}/miss {$vidMiss}, thumbnails {$thumb}/miss {$thumbMiss})");

        return self::SUCCESS;
    }

    /** Copy a source public-relative file to a destination public-relative path. */
    private function copyFile(?string $srcRel, string $destRel): bool
    {
        if (!$srcRel || str_starts_with($srcRel, 'http')) {
            return false;
        }
        $src = public_path($srcRel);
        if (!File::exists($src)) {
            return false;
        }
        $destDir = dirname(public_path($destRel));
        if (!File::isDirectory($destDir)) {
            File::makeDirectory($destDir, 0755, true, true);
        }
        File::copy($src, public_path($destRel));
        return true;
    }
}
