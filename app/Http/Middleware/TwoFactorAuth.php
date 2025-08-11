<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->hasTwoFactorEnabled()) {
            // Check if user completed 2FA in current session
            if (!$request->session()->has('2fa_verified')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Autenticación de dos factores requerida',
                        'requires_2fa' => true
                    ], 403);
                }

                return redirect()->route('auth.two-factor');
            }
        }

        return $next($request);
    }
} 