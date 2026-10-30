<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LearnupWord extends Model
{
    use HasFactory;

    protected $table = 'learnup_words';

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

    protected $appends = [
        'video_url',
        'thumbnail_url',
        'parsed_json',
        'video_relative_path',
        'thumbnail_relative_path',
    ];

    /**
     * In-memory cache for category slugs to avoid N+1 queries.
     */
    protected static ?array $categorySlugCache = null;

    /**
     * Clear category slug cache.
     */
    public static function clearCategorySlugCache(): void
    {
        self::$categorySlugCache = null;
    }

    /**
     * Resolve the category folder slug for a given category ID.
     */
    public static function resolveCategorySlug(?int $categoryId): string
    {
        if (!$categoryId) {
            return 'general';
        }

        if (self::$categorySlugCache === null) {
            self::$categorySlugCache = [];
            try {
                $categories = LearnupCategory::all(['id', 'name']);
                foreach ($categories as $cat) {
                    $slug = Str::slug($cat->name) ?: 'general';
                    $base = public_path('uploads/learnup_with_ai');
                    // Check if folder on disk is named 'tecnology' instead of 'technology'
                    if (!File::isDirectory($base . '/' . $slug)) {
                        if ($slug === 'technology' && File::isDirectory($base . '/tecnology')) {
                            $slug = 'tecnology';
                        }
                    }
                    self::$categorySlugCache[$cat->id] = $slug;
                }
            } catch (\Throwable $e) {
                return 'general';
            }
        }

        return self::$categorySlugCache[$categoryId] ?? 'general';
    }

    /**
     * LearnupCategory this video belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(LearnupCategory::class, 'category_id');
    }

    /**
     * Mutator to ensure only the filename is stored in video_path.
     */
    public function setVideoPathAttribute($value): void
    {
        if ($value && !str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
            $this->attributes['video_path'] = basename(str_replace('\\', '/', $value));
        } else {
            $this->attributes['video_path'] = $value;
        }
    }

    /**
     * Mutator to ensure only the filename is stored in thumbnail_path.
     */
    public function setThumbnailPathAttribute($value): void
    {
        if ($value && !str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
            $this->attributes['thumbnail_path'] = basename(str_replace('\\', '/', $value));
        } else {
            $this->attributes['thumbnail_path'] = $value;
        }
    }

    /**
     * Get the relative disk path for the video.
     */
    public function getVideoRelativePath(): ?string
    {
        if (!$this->video_path) {
            return null;
        }

        if (str_starts_with($this->video_path, 'http://') || str_starts_with($this->video_path, 'https://')) {
            return $this->video_path;
        }

        // If it already contains a slash, it is already a path
        if (str_contains($this->video_path, '/')) {
            return $this->video_path;
        }

        $slug = self::resolveCategorySlug($this->category_id);
        $categoryPath = 'uploads/learnup_with_ai/' . $slug . '/videos/' . $this->video_path;

        if (File::exists(public_path($categoryPath))) {
            return $categoryPath;
        }

        // Check if legacy folder has it
        $legacyPath = 'uploads/learnup/videos/' . $this->video_path;
        if (File::exists(public_path($legacyPath))) {
            return $legacyPath;
        }

        // Check if technology variation folder has it
        if ($slug === 'technology' && File::exists(public_path('uploads/learnup_with_ai/tecnology/videos/' . $this->video_path))) {
            return 'uploads/learnup_with_ai/tecnology/videos/' . $this->video_path;
        }

        return $categoryPath;
    }

    /**
     * Accessor for video_relative_path.
     */
    public function getVideoRelativePathAttribute(): ?string
    {
        return $this->getVideoRelativePath();
    }

    /**
     * Get the relative disk path for the thumbnail.
     */
    public function getThumbnailRelativePath(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        if (str_starts_with($this->thumbnail_path, 'http://') || str_starts_with($this->thumbnail_path, 'https://')) {
            return $this->thumbnail_path;
        }

        if (str_contains($this->thumbnail_path, '/')) {
            return $this->thumbnail_path;
        }

        $slug = self::resolveCategorySlug($this->category_id);
        $categoryPath = 'uploads/learnup_with_ai/' . $slug . '/thumbnails/' . $this->thumbnail_path;

        if (File::exists(public_path($categoryPath))) {
            return $categoryPath;
        }

        $legacyPath = 'uploads/learnup/thumbnails/' . $this->thumbnail_path;
        if (File::exists(public_path($legacyPath))) {
            return $legacyPath;
        }

        if ($slug === 'technology' && File::exists(public_path('uploads/learnup_with_ai/tecnology/thumbnails/' . $this->thumbnail_path))) {
            return 'uploads/learnup_with_ai/tecnology/thumbnails/' . $this->thumbnail_path;
        }

        return $categoryPath;
    }

    /**
     * Accessor for thumbnail_relative_path.
     */
    public function getThumbnailRelativePathAttribute(): ?string
    {
        return $this->getThumbnailRelativePath();
    }

    /**
     * Get full public URL for video.
     */
    public function getVideoUrlAttribute(): ?string
    {
        $rel = $this->getVideoRelativePath();
        if (!$rel) {
            return null;
        }

        if (str_starts_with($rel, 'http://') || str_starts_with($rel, 'https://')) {
            return $rel;
        }

        return asset($rel);
    }

    /**
     * Get full public URL for thumbnail.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $rel = $this->getThumbnailRelativePath();
        if (!$rel) {
            return null;
        }

        if (str_starts_with($rel, 'http://') || str_starts_with($rel, 'https://')) {
            return $rel;
        }

        return asset($rel);
    }

    /**
     * Get parsed JSON or array representation.
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

        $vRel = $this->getVideoRelativePath();
        if ($vRel && !str_starts_with($vRel, 'http') && File::exists(public_path($vRel))) {
            File::delete(public_path($vRel));
            $touchedDirs[] = dirname(public_path($vRel));
        }

        $tRel = $this->getThumbnailRelativePath();
        if ($tRel && !str_starts_with($tRel, 'http') && File::exists(public_path($tRel))) {
            File::delete(public_path($tRel));
            $touchedDirs[] = dirname(public_path($tRel));
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
     * sub-folders. Scoped strictly to the learnup_with_ai tree for safety.
     */
    public static function removeDirIfEmpty(?string $absoluteDir): void
    {
        if (!$absoluteDir) {
            return;
        }

        $normalized = str_replace('\\', '/', $absoluteDir);
        if (!str_contains($normalized, '/learnup_with_ai/')) {
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
     * Booted model events for auto file cleanup on delete.
     */
    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->deleteAssociatedFiles();
        });
    }
}
