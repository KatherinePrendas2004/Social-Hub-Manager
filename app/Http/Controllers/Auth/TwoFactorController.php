<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorRequest;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TwoFactorController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show two-factor authentication setup.
     */
    public function show()
    {
        return view('auth.two-factor');
    }

    /**
     * Toggle two-factor authentication.
     */
    public function toggle(Request $request)
    {
        try {
            // Log para debug
            Log::info('2FA Toggle Request', [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
                'enable_value' => $request->input('enable'),
                'enable_type' => gettype($request->input('enable')),
                'code_value' => $request->input('two_factor_code'),
                'code_length' => strlen($request->input('two_factor_code', ''))
            ]);

            // Validación manual para mejor control
            $request->validate([
                'enable' => 'required|string|in:true,false',
                'two_factor_code' => 'nullable|string|size:6|regex:/^[0-9]{6}$/'
            ]);

            $user = Auth::user();
            $enable = $request->input('enable') === 'true';

            if ($enable && !$user->two_factor_enabled) {
                return $this->enableTwoFactor($request, $user);
            } elseif (!$enable && $user->two_factor_enabled) {
                return $this->disableTwoFactor($request, $user);
            }

            return response()->json([
                'success' => false,
                'error' => 'Estado de 2FA ya configurado'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in toggleTwoFactor: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor. Por favor intenta nuevamente.'
            ], 500);
        }
    }

    /**
     * Enable two-factor authentication.
     */
    private function enableTwoFactor(Request $request, $user)
    {
        try {
            // Si no hay secreto temporal, generamos uno nuevo
            if (!$request->session()->has('temp_2fa_secret')) {
                $secret = $this->twoFactorService->generateSecret();
                $request->session()->put('temp_2fa_secret', $secret);
                
                $qrCodeUrl = $this->twoFactorService->generateQrCodeUrl($user, $secret);

                return response()->json([
                    'success' => false,
                    'needs_verification' => true,
                    'message' => 'Escanea el código QR y confirma con el código de 6 dígitos',
                    'qr_code_url' => $qrCodeUrl,
                    'secret' => $secret
                ]);
            }

            // Verificamos que se haya proporcionado el código
            if (!$request->has('two_factor_code') || empty($request->input('two_factor_code'))) {
                return response()->json([
                    'success' => false,
                    'error' => 'Código de verificación requerido'
                ], 400);
            }

            $tempSecret = $request->session()->get('temp_2fa_secret');
            $recoveryCodes = $this->twoFactorService->enable($user, $tempSecret, $request->input('two_factor_code'));

            if (!$recoveryCodes) {
                return response()->json([
                    'success' => false,
                    'error' => 'Código de verificación incorrecto. Intenta de nuevo.'
                ], 400);
            }

            $request->session()->forget('temp_2fa_secret');

            return response()->json([
                'success' => true,
                'message' => 'Autenticación de dos factores activada exitosamente',
                'recovery_codes' => $recoveryCodes
            ]);

        } catch (\Exception $e) {
            Log::error('Error enabling 2FA: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al activar 2FA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disable two-factor authentication.
     */
    private function disableTwoFactor(Request $request, $user)
    {
        try {
            if (!$request->has('two_factor_code') || empty($request->input('two_factor_code'))) {
                return response()->json([
                    'success' => false,
                    'error' => 'Código de verificación requerido para desactivar 2FA'
                ], 400);
            }

            if (!$this->twoFactorService->disable($user, $request->input('two_factor_code'))) {
                return response()->json([
                    'success' => false,
                    'error' => 'Código de verificación incorrecto'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Autenticación de dos factores desactivada'
            ]);

        } catch (\Exception $e) {
            Log::error('Error disabling 2FA: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al desactivar 2FA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->two_factor_enabled) {
                return response()->json([
                    'success' => false,
                    'error' => 'La autenticación de dos factores no está habilitada'
                ], 400);
            }

            $recoveryCodes = $this->twoFactorService->regenerateRecoveryCodes($user);

            return response()->json([
                'success' => true,
                'recovery_codes' => $recoveryCodes,
                'message' => 'Códigos de recuperación regenerados'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error regenerating recovery codes: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'error' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
}