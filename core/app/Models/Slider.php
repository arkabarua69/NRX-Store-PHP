<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Slider extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const PLACEHOLDER_IMAGE_PATH = 'assets/template/images/placeholder.jpeg';

    protected $fillable = [
        'url',
        'order_column',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = [
        'image_url'
    ];

    public function getImageUrlAttribute(): string
    {
        return $this->hasMedia()
            ? $this->getFirstMediaUrl()
            : self::PLACEHOLDER_IMAGE_PATH;
    }
}
