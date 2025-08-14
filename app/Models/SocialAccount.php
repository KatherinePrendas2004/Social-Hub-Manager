<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'platform_user_id',
        'platform_username',
        'access_token',
        'refresh_token',
        'expires_at',
        'is_active',
        'connected_at',
        'disconnected_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the user that owns this social account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get decrypted access token.
     */
    public function getDecryptedAccessToken()
    {
        return $this->access_token ? decrypt($this->access_token) : null;
    }

    /**
     * Get decrypted refresh token.
     */
    public function getDecryptedRefreshToken()
    {
        return $this->refresh_token ? decrypt($this->refresh_token) : null;
    }
}