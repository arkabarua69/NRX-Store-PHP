<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const PLACEHOLDER_IMAGE_PATH = 'assets/template/images/placeholder.jpeg';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'type',
        'percentage',
        'uid_checker',
        'status',
        'has_tutorial',
        'tutorial_link',
        'tutorial_text',
    ];

    protected $casts = [
        'status' => 'boolean',
        'has_tutorial' => 'boolean',
        'uid_checker' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->hasMedia()
        ? $this->getFirstMediaUrl()
        : self::PLACEHOLDER_IMAGE_PATH;
    }

    public function isVoucher(): bool
    {
        return $this->type === Status::VOUCHER;
    }

    public function isInGame(): bool
    {
        return $this->type === Status::INGAME;
    }

    public function isTopup(): bool
    {
        return $this->type === Status::TOPUP;
    }
}
