<?php

namespace App\Traits;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;

trait HasTwoFactorAuth
{
    /**
     * Verify a two-factor authentication code.
     */
    public function verifyTwoFactorCode($code)
    {
        if (!$this->two_factor_secret) {
            return false;
        }

        $google2fa = new Google2FA();
        $secret = decrypt($this->two_factor_secret);
        
        return $google2fa->verifyKey($secret, $code);
    }

    /**
     * Verify a recovery code.
     */
    public function verifyRecoveryCode($code)
    {
        if (!$this->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = json_decode(decrypt($this->two_factor_recovery_codes), true);
        
        if (in_array($code, $recoveryCodes)) {
            // Remove used recovery code
            $recoveryCodes = array_filter($recoveryCodes, function($recoveryCode) use ($code) {
                return $recoveryCode !== $code;
            });

            $this->update([
                'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes)))
            ]);

            return true;
        }

        return false;
    }

    /**
     * Generate recovery codes.
     */
    public function generateRecoveryCodes()
    {
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(Str::random(8));
        }
        
        return $recoveryCodes;
    }

    /**
     * Check if 2FA is enabled.
     */
    public function hasTwoFactorEnabled()
    {
        return $this->two_factor_enabled && $this->two_factor_secret;
    }
}