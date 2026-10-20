<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'image_path',
        'is_active',
        'order_index',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'order_index' => 'integer',
    ];

    protected $appends = [
        'image_url',
        'image_relative_path',
    ];

    /**
     * Videos that belong to this category.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(VideoLearningWord::class, 'category_id');
    }

    /**
     * Mutator to ensure only the filename is stored in the database.
     */
    public function setImagePathAttribute($value): void
    {
        if ($value && !str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
            $this->attributes['image_path'] = basename(str_replace('\\', '/', $value));
        } else {
            $this->attributes['image_path'] = $value;
        }
    }

    /**
     * Get the relative path for the category image on disk.
     */
    public function getImageRelativePath(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (str_contains($this->image_path, '/')) {
            return $this->image_path;
        }

        $slug = Str::slug($this->name) ?: 'general';
        $categoryDir = 'uploads/video_learning_with_word/' . $slug . '/category';

        if (File::exists(public_path($categoryDir . '/' . $this->image_path))) {
            return $categoryDir . '/' . $this->image_path;
        }

        // Support 'tecnology' variation if folder exists on disk
        if ($slug === 'technology' && File::exists(public_path('uploads/video_learning_with_word/tecnology/category/' . $this->image_path))) {
            return 'uploads/video_learning_with_word/tecnology/category/' . $this->image_path;
        }

        return $categoryDir . '/' . $this->image_path;
    }

    /**
     * Accessor for image_relative_path
     */
    public function getImageRelativePathAttribute(): ?string
    {
        return $this->getImageRelativePath();
    }

    /**
     * Full public URL for the category image.
     */
    public function getImageUrlAttribute(): ?string
    {
        $rel = $this->getImageRelativePath();
        if (!$rel) {
            return null;
        }

        if (str_starts_with($rel, 'http://') || str_starts_with($rel, 'https://')) {
            return $rel;
        }

        return asset($rel);
    }

    /**
     * Delete associated physical image file from public/uploads folder.
     */
    public function deleteAssociatedFiles(): void
    {
        $rel = $this->getImageRelativePath();
        if ($rel && !str_starts_with($rel, 'http') && File::exists(public_path($rel))) {
            File::delete(public_path($rel));
            VideoLearningWord::removeDirIfEmpty(dirname(public_path($rel)));
        }

        $base = public_path('uploads/video_learning_with_word/' . (Str::slug($this->name) ?: 'general'));
        VideoLearningWord::removeDirIfEmpty($base);
    }

    /**
     * Booted model events for auto file cleanup on delete and cache clearing.
     */
    protected static function booted()
    {
        static::saved(function () {
            VideoLearningWord::clearCategorySlugCache();
        });

        static::deleted(function () {
            VideoLearningWord::clearCategorySlugCache();
        });

        static::deleting(function ($model) {
            $model->deleteAssociatedFiles();
        });
    }
}
