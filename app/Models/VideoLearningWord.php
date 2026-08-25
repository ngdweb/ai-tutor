<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class VideoLearningWord extends Model
{
    use HasFactory;

    protected $table = 'video_learning_words';

    protected $fillable = [
        'title',
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
    ];

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
     * Delete associated physical files from public/uploads folder
     */
    public function deleteAssociatedFiles(): void
    {
        if ($this->video_path && File::exists(public_path($this->video_path))) {
            File::delete(public_path($this->video_path));
        }

        if ($this->thumbnail_path && File::exists(public_path($this->thumbnail_path))) {
            File::delete(public_path($this->thumbnail_path));
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
