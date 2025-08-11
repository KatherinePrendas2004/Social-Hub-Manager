<?php

namespace App\Services\Auth;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TwoFactorService
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for 2FA.
     */
    public function generateSecret()
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generate QR code URL for 2FA setup.
     */
    public function generateQrCodeUrl($user, $secret)
    {
        $companyName = config('app.name', 'Social Hub Manager');
        
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            $companyName,
            $user->email,
            $secret
        );

        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCodeUrl);
    }

    /**
     * Enable 2FA for a user.
     */
    public function enable($user, $secret, $verificationCode)
    {
        if (!$this->google2fa->verifyKey($secret, $verificationCode)) {
            return false;
        }

        $recoveryCodes = $user->generateRecoveryCodes();

        DB::transaction(function () use ($user, $secret, $recoveryCodes) {
            $user->update([
                'two_factor_secret' => encrypt($secret),
                'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
                'two_factor_enabled' => true,
            ]);
        });

        return $recoveryCodes;
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable($user, $verificationCode)
    {
        if (!$user->verifyTwoFactorCode($verificationCode)) {
            return false;
        }

        DB::transaction(function () use ($user) {
            $user->update([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_enabled' => false,
            ]);
        });

        return true;
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes($user)
    {
        $recoveryCodes = $user->generateRecoveryCodes();
        
        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes))
        ]);

        return $recoveryCodes;
    }

    /**
     * Verify 2FA code or recovery code.
     */
    public function verify($user, $code)
    {
        if (strlen($code) === 6) {
            return $user->verifyTwoFactorCode($code);
        }
        
        if (strlen($code) === 8) {
            return $user->verifyRecoveryCode($code);
        }

        return false;
    }
}