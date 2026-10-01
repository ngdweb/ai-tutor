<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;

class VideoLearningWord extends Model
{
    use HasFactory;

    protected $table = 'video_learning_words';

    protected $fillable = [
        'category_id',
        'title',
        'episode_no',
        'video_path',
        'video_name',
        'thumbnail_path',
        'json_data',
        'is_visible',
        'order_index',
    ];

    protected $casts = [
        'is_visible'  => 'boolean',
        'order_index' => 'integer',
        'category_id' => 'integer',
        'episode_no'  => 'integer',
    ];

    /**
     * Category this video belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    protected $appends = [
        'video_url',
        'thumbnail_url',
        'parsed_json',
    ];

    /**
     * Get full public URL for video
     */
    public function getVideoUrlAttribute(): ?string
    {
        if (!$this->video_path) {
            return null;
        }

        if (str_starts_with($this->video_path, 'http://') || str_starts_with($this->video_path, 'https://')) {
            return $this->video_path;
        }

        return asset($this->video_path);
    }

    /**
     * Get full public URL for thumbnail
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        if (str_starts_with($this->thumbnail_path, 'http://') || str_starts_with($this->thumbnail_path, 'https://')) {
            return $this->thumbnail_path;
        }

        return asset($this->thumbnail_path);
    }

    /**
     * Get parsed JSON or array representation
     */
    public function getParsedJsonAttribute()
    {
        if (empty($this->json_data)) {
            return null;
        }

        $decoded = json_decode($this->json_data, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $this->json_data;
    }

    /**
     * Delete associated physical files, then clean up any folders left empty.
     */
    public function deleteAssociatedFiles(): void
    {
        $touchedDirs = [];

        if ($this->video_path && !str_starts_with($this->video_path, 'http') && File::exists(public_path($this->video_path))) {
            File::delete(public_path($this->video_path));
            $touchedDirs[] = dirname(public_path($this->video_path));
        }

        if ($this->thumbnail_path && !str_starts_with($this->thumbnail_path, 'http') && File::exists(public_path($this->thumbnail_path))) {
            File::delete(public_path($this->thumbnail_path));
            $touchedDirs[] = dirname(public_path($this->thumbnail_path));
        }

        self::cleanupEmptyFolders($touchedDirs);
    }

    /**
     * For each given folder, remove it (and its category parent) if it is empty.
     */
    public static function cleanupEmptyFolders(array $absoluteDirs): void
    {
        foreach (array_unique($absoluteDirs) as $dir) {
            self::removeDirIfEmpty($dir);          // e.g. .../<category>/videos
            self::removeDirIfEmpty(dirname($dir));  // e.g. .../<category> (category root)
        }
    }

    /**
     * Delete a directory only when it contains fewer than 1 file (empty) and no
     * sub-folders. Scoped strictly to the video_learning_with_word tree for safety.
     */
    public static function removeDirIfEmpty(?string $absoluteDir): void
    {
        if (!$absoluteDir) {
            return;
        }

        $normalized = str_replace('\\', '/', $absoluteDir);
        if (!str_contains($normalized, '/video_learning_with_word/')) {
            return; // never touch anything outside our module folder
        }

        if (!File::isDirectory($absoluteDir)) {
            return;
        }

        $hasFiles   = count(File::files($absoluteDir)) > 0;
        $hasFolders = count(File::directories($absoluteDir)) > 0;

        if (!$hasFiles && !$hasFolders) {
            File::deleteDirectory($absoluteDir);
        }
    }

    /**
     * Booted model events for auto file cleanup on delete
     */
    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->deleteAssociatedFiles();
        });
    }
}
