<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasTwoFactorAuth;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasTwoFactorAuth;

    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_enabled',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
        ];
    }

    // Relaciones
    public function socialAccounts()
    {
        return $this->hasMany(\App\Models\SocialAccount::class);
    }

    // NUEVA RELACIÓN PARA POSTS
    public function posts()
    {
        return $this->hasMany(\App\Models\Post::class);
    }
}