<?php

namespace App\Models;

use Filament\Panel;
use App\Constants\Role;
use App\Constants\Status;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'balance',
        'password',
        'gauth_id',
        'is_reseller',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_reseller'   => 'boolean',
        'status' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function getRedirectRoute()
    {
        return match ($this->role) {
            Role::USER => '/',
            Role::ADMIN => '/admin'
        };
    }

    public function isAdmin()
    {
        return $this->role === Role::ADMIN;
    }

    public function isUser()
    {
        return $this->role === Role::USER;
    }

    public function isBanned()
    {
        return (int) $this->status === Status::INACTIVE;
    }

    public function isReseller()
    {
        return (int) $this->is_reseller === Status::ACTIVE;
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
