<?php

namespace App\Models;

use App\Constants\MenuType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'link',
        'icon',
        'type',
        'order_column',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function isUser(): bool
    {
        return $this->type === MenuType::USER;
    }

    public function isGuest(): bool
    {
        return $this->type === MenuType::GUEST;
    }

    public function isBoth(): bool
    {
        return $this->type === MenuType::BOTH;
    }
}
