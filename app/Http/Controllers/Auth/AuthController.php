<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Show registration form.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle user login.
     */
    public function login(LoginRequest $request)
    {
        Log::info('Login attempt started', ['email' => $request->email]);

        $throttleKey = Str::lower($request->email);
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Demasiados intentos fallidos. Intenta de nuevo en {$seconds} segundos."
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 300);
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales proporcionadas no coinciden con nuestros registros.'
            ], 401);
        }

        // Check if 2FA is required
        if ($user->hasTwoFactorEnabled()) {
            if (!$request->filled('two_factor_code')) {
                $request->session()->put('login_credentials', [
                    'user_id' => $user->id,
                    'email' => $request->email,
                    'password' => $request->password
                ]);
                
                return response()->json([
                    'success' => false,
                    'requires_2fa' => true,
                    'message' => 'Ingresa el código de tu aplicación autenticadora'
                ], 200);
            }

            if (!$user->verifyTwoFactorCode($request->two_factor_code) && 
                !$user->verifyRecoveryCode($request->two_factor_code)) {
                RateLimiter::hit($throttleKey, 300);
                return response()->json([
                    'success' => false,
                    'message' => 'El código de autenticación es inválido.'
                ], 401);
            }
        }

        Auth::login($user);

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        RateLimiter::clear($throttleKey);
        $request->session()->forget('login_credentials');

        Log::info('User logged in successfully', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard.index')
        ]);
    }

    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        Auth::login($user);

        Log::info('New user registered', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard.index')
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('message', 'Sesión cerrada exitosamente.');
    }
}