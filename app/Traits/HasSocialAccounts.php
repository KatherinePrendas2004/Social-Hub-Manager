<?php

namespace App\Traits;

use App\Models\SocialAccount;

trait HasSocialAccounts
{
    /**
     * Get all social accounts for this user.
     */
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get a specific social account by platform.
     */
    public function getSocialAccount($platform)
    {
        return $this->socialAccounts()->where('platform', $platform)->first();
    }

    /**
     * Check if user has connected a specific platform.
     */
    public function hasConnectedPlatform($platform)
    {
        return $this->socialAccounts()
            ->where('platform', $platform)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all connected platforms.
     */
    public function getConnectedPlatforms()
    {
        return $this->socialAccounts()
            ->where('is_active', true)
            ->pluck('platform')
            ->toArray();
    }

    /**
     * Connect a social account.
     */
    public function connectSocialAccount($platform, $platformUserId, $accessToken, $refreshToken = null, $expiresAt = null)
    {
        return $this->socialAccounts()->updateOrCreate(
            [
                'platform' => $platform,
                'platform_user_id' => $platformUserId
            ],
            [
                'access_token' => encrypt($accessToken),
                'refresh_token' => $refreshToken ? encrypt($refreshToken) : null,
                'expires_at' => $expiresAt,
                'is_active' => true,
                'connected_at' => now(),
            ]
        );
    }

    /**
     * Disconnect a social account.
     */
    public function disconnectSocialAccount($platform)
    {
        return $this->socialAccounts()
            ->where('platform', $platform)
            ->update(['is_active' => false, 'disconnected_at' => now()]);
    }

    /**
     * Get active social accounts count.
     */
    public function getActiveSocialAccountsCount()
    {
        return $this->socialAccounts()->where('is_active', true)->count();
    }
}