<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogCategoryVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'title',
        'video_url',
        'video_file',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the formatted platform name with uppercase first letter.
     */
    public function getFormattedPlatformAttribute(): string
    {
        return ucfirst($this->platform ?? 'other');
    }

    /**
     * Check if this record relies on a uploaded file.
     */
    public function getIsUploadedFileAttribute(): bool
    {
        return ! empty($this->video_file);
    }

    /**
     * Get playable or embeddable URL.
     */
    public function getPlayableUrlAttribute(): string
    {
        if ($this->video_file) {
            if (str_starts_with($this->video_file, 'http://') || str_starts_with($this->video_file, 'https://')) {
                return $this->video_file;
            }
            return asset('storage/' . ltrim($this->video_file, '/'));
        }

        $url = trim((string) $this->video_url);

        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=1&rel=0';
        }

        if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/i', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[3] . '?autoplay=1';
        }

        return $url;
    }

    /**
     * Check if URL is an embeddable iframe (like YouTube / Vimeo).
     */
    public function getIsEmbeddableAttribute(): bool
    {
        if (! empty($this->video_file)) {
            return false;
        }

        $url = strtolower(trim((string) $this->video_url));

        return str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be') || str_contains($url, 'vimeo.com');
    }
}
