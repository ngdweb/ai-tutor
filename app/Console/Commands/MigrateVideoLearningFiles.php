<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\VideoLearningWord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrateVideoLearningFiles extends Command
{
    /**
     * Copy legacy flat-path files into the per-category structure
     *   public/uploads/video_learning_with_word/<category>/{videos,thumbnails,category}
     * and update each record's stored path. Idempotent: records already in the
     * new structure are skipped. Use --move to delete the old file after copying.
     */
    protected $signature = 'video-learning:migrate-files {--move : Move files (delete originals) instead of copying}';

    protected $description = 'Relocate existing video/thumbnail/category-image files into the per-category folder structure';

    public function handle(): int
    {
        $move = (bool) $this->option('move');
        $action = $move ? 'Moving' : 'Copying';
        $this->info("{$action} video learning files into uploads/video_learning_with_word/<category>/ ...");

        $videoCount = 0;
        $thumbCount = 0;
        $imageCount = 0;
        $skipped    = 0;

        VideoLearningWord::with('category')->chunkById(100, function ($items) use ($move, &$videoCount, &$thumbCount, &$skipped) {
            foreach ($items as $item) {
                $slug = Str::slug(optional($item->category)->name ?? 'General') ?: 'general';
                $base = 'uploads/video_learning_with_word/' . $slug;

                $changed = false;

                // Video
                $newVideo = $this->relocate($item->video_path, $base . '/videos', $move);
                if ($newVideo && $newVideo !== $item->video_path) {
                    $item->video_path = $newVideo;
                    $videoCount++;
                    $changed = true;
                } elseif ($newVideo === $item->video_path) {
                    $skipped++;
                }

                // Thumbnail
                $newThumb = $this->relocate($item->thumbnail_path, $base . '/thumbnails', $move);
                if ($newThumb && $newThumb !== $item->thumbnail_path) {
                    $item->thumbnail_path = $newThumb;
                    $thumbCount++;
                    $changed = true;
                }

                if ($changed) {
                    $item->saveQuietly(); // don't fire model events / touch timestamps
                }
            }
        });

        // Category images
        foreach (Category::all() as $category) {
            $slug = Str::slug($category->name) ?: 'general';
            $newImage = $this->relocate($category->image_path, 'uploads/video_learning_with_word/' . $slug . '/category', $move);
            if ($newImage && $newImage !== $category->image_path) {
                $category->image_path = $newImage;
                $category->saveQuietly();
                $imageCount++;
            }
        }

        $this->info("Done. Videos: {$videoCount}, Thumbnails: {$thumbCount}, Category images: {$imageCount}, already-in-place: {$skipped}.");

        return self::SUCCESS;
    }

    /**
     * Copy/move a single stored file into $targetRelDir and return its new
     * relative path. Returns the original path unchanged when already in place,
     * when empty, when a remote URL, or when the physical file is missing.
     */
    private function relocate(?string $relPath, string $targetRelDir, bool $move): ?string
    {
        if (!$relPath || str_starts_with($relPath, 'http')) {
            return $relPath;
        }

        $currentDir = trim(str_replace('\\', '/', dirname($relPath)), '/');
        if ($currentDir === trim($targetRelDir, '/')) {
            return $relPath; // already in the new structure
        }

        $src = public_path($relPath);
        if (!File::exists($src)) {
            $this->warn("  Missing file, skipped: {$relPath}");
            return $relPath;
        }

        $dir = public_path($targetRelDir);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $fileName = basename($relPath);
        if (File::exists($dir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext  = pathinfo($fileName, PATHINFO_EXTENSION);
            $name = pathinfo($fileName, PATHINFO_FILENAME);
            $fileName = $name . '_' . time() . ($ext ? '.' . $ext : '');
        }

        $dest = $dir . DIRECTORY_SEPARATOR . $fileName;

        if ($move) {
            File::move($src, $dest);
        } else {
            File::copy($src, $dest);
        }

        return $targetRelDir . '/' . $fileName;
    }
}
