<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Popup extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    
    protected $fillable = [
        'url',
        'content',
        'button_text',
        'type',
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
            : '';
    }
}
