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
    ];

    /**
     * Videos that belong to this category.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(VideoLearningWord::class, 'category_id');
    }

    /**
     * Full public URL for the category image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return asset($this->image_path);
    }

    /**
     * Delete associated physical image file from public/uploads folder.
     */
    public function deleteAssociatedFiles(): void
    {
        if ($this->image_path && !str_starts_with($this->image_path, 'http') && File::exists(public_path($this->image_path))) {
            File::delete(public_path($this->image_path));
            // Remove the category image folder if it is now empty.
            VideoLearningWord::removeDirIfEmpty(dirname(public_path($this->image_path)));
        }

        // Finally, remove the whole category folder if nothing remains in it.
        $base = public_path('uploads/video_learning_with_word/' . (Str::slug($this->name) ?: 'general'));
        VideoLearningWord::removeDirIfEmpty($base);
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
