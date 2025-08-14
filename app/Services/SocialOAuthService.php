<?php
namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SocialOAuthService
{
    public function getAuthUrl(string $platform)
    {
        $state = csrf_token().'|'.Str::random(16);

        switch($platform) {
            case 'twitter':
                $codeVerifier = $this->generateCodeVerifier();
                $codeChallenge = $this->generateCodeChallenge($codeVerifier);
                
                session(['twitter_code_verifier' => $codeVerifier]);
                
                $params = [
                    'response_type' => 'code',
                    'client_id' => config('services.twitter.client_id'),
                    'redirect_uri' => config('services.twitter.redirect'),
                    'scope' => 'tweet.read tweet.write users.read offline.access',
                    'state' => $state,
                    'code_challenge' => $codeChallenge,
                    'code_challenge_method' => 'S256',
                ];
                return 'https://twitter.com/i/oauth2/authorize?'.http_build_query($params);

            case 'linkedin':
                $params = [
                    'response_type' => 'code',
                    'client_id' => config('services.linkedin.client_id'),
                    'redirect_uri' => config('services.linkedin.redirect'),
                    // Estos son los scopes que tienes habilitados en tu LinkedIn Developer Console
                    'scope' => 'openid profile email w_member_social',
                    'state' => $state,
                ];
                return 'https://www.linkedin.com/oauth/v2/authorization?'.http_build_query($params);

            case 'reddit':
                $params = [
                    'client_id' => config('services.reddit.client_id'),
                    'response_type' => 'code',
                    'state' => $state,
                    'redirect_uri' => config('services.reddit.redirect'),
                    'duration' => 'permanent',
                    'scope' => 'submit identity',
                ];
                return 'https://www.reddit.com/api/v1/authorize?'.http_build_query($params);

            default:
                throw new \InvalidArgumentException("Provider no soportado");
        }
    }

    public function handleCallback(string $platform, string $code)
    {
        $user = Auth::user();
        $now = Carbon::now();

        switch($platform) {
            case 'twitter':
                // Twitter requiere HTTP Basic Auth con client_id:client_secret
                $resp = Http::withBasicAuth(
                        config('services.twitter.client_id'), 
                        config('services.twitter.client_secret')
                    )
                    ->asForm()
                    ->post('https://api.twitter.com/2/oauth2/token', [
                        'code' => $code,
                        'grant_type' => 'authorization_code',
                        'redirect_uri' => config('services.twitter.redirect'),
                        'code_verifier' => session('twitter_code_verifier'),
                    ])->json();
                
                $access = $resp['access_token'] ?? null;
                $refresh = $resp['refresh_token'] ?? null;
                $expiresIn = $resp['expires_in'] ?? null;
                $providerUserId = null;
                $username = null;

                // Obtener información del usuario de Twitter
                if ($access) {
                    try {
                        $userInfo = Http::withToken($access)
                            ->get('https://api.twitter.com/2/users/me', [
                                'user.fields' => 'id,name,username'
                            ])->json();

                        if (isset($userInfo['data'])) {
                            $providerUserId = $userInfo['data']['id'] ?? null;
                            $username = $userInfo['data']['username'] ?? $userInfo['data']['name'] ?? null;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error obteniendo perfil de Twitter: ' . $e->getMessage());
                        $providerUserId = 'unknown';
                        $username = 'Twitter User';
                    }
                }

                // Limpiar el code_verifier de la sesión
                session()->forget('twitter_code_verifier');
                break;

            case 'linkedin':
                // Obtener access token
                $resp = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => config('services.linkedin.redirect'),
                    'client_id' => config('services.linkedin.client_id'),
                    'client_secret' => config('services.linkedin.client_secret'),
                ])->json();

                $access = $resp['access_token'] ?? null;
                $refresh = null; // LinkedIn no usa refresh tokens en este flujo
                $expiresIn = $resp['expires_in'] ?? null;
                
                $providerUserId = null;
                $username = null;

                // Obtener información del perfil usando la nueva API
                if ($access) {
                    try {
                        // Obtener información básica del perfil
                        $profile = Http::withToken($access)
                            ->get('https://api.linkedin.com/v2/userinfo')
                            ->json();

                        $providerUserId = $profile['sub'] ?? null; // 'sub' es el ID del usuario en OpenID Connect
                        $username = $profile['name'] ?? null; // Nombre completo
                        
                        // Si no hay nombre completo, construirlo
                        if (!$username) {
                            $firstName = $profile['given_name'] ?? '';
                            $lastName = $profile['family_name'] ?? '';
                            $username = trim($firstName . ' ' . $lastName);
                        }

                        // Si aún no hay username, usar email como fallback
                        if (!$username) {
                            $username = $profile['email'] ?? 'LinkedIn User';
                        }

                    } catch (\Exception $e) {
                        // Log del error pero continúa con el flujo
                        \Log::error('Error obteniendo perfil de LinkedIn: ' . $e->getMessage());
                        $providerUserId = 'unknown';
                        $username = 'LinkedIn User';
                    }
                }
                break;

            case 'reddit':
            $resp = Http::withBasicAuth(config('services.reddit.client_id'), config('services.reddit.client_secret'))
                ->withHeaders(['User-Agent' => 'SocialHubManager/1.0 by YourUsername'])
                ->asForm()
                ->post('https://www.reddit.com/api/v1/access_token', [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => config('services.reddit.redirect'),
                ])->json();
            
            $access = $resp['access_token'] ?? null;
            $refresh = $resp['refresh_token'] ?? null;
            $expiresIn = $resp['expires_in'] ?? null;
            
            $providerUserId = null;
            $username = null;
            
            if ($access) {
                try {
                    $userInfo = Http::withToken($access)
                        ->withHeaders(['User-Agent' => 'SocialHubManager/1.0 by YourUsername'])
                        ->get('https://oauth.reddit.com/api/v1/me')
                        ->json();
                    
                    $providerUserId = $userInfo['id'] ?? null;
                    $username = $userInfo['name'] ?? null;
                    
                    \Log::info('Reddit user info obtained', [
                        'user_id' => $providerUserId,
                        'username' => $username
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Error obteniendo información del usuario de Reddit: ' . $e->getMessage());
                    $providerUserId = 'unknown_' . time();
                    $username = 'Usuario de Reddit';
                }
            }
            break;

        default:
            throw new \InvalidArgumentException("Provider no soportado");

        }

        if (!$access) {
            \Log::error("No se pudo obtener access token para $platform", [
                'response' => $resp ?? null
            ]);
            return false;
        }

        // Guardar en DB, encriptando tokens
        SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'platform' => $platform
            ],
            [
                'platform_user_id' => $providerUserId,
                'platform_username' => $username,
                'access_token' => encrypt($access),
                'refresh_token' => $refresh ? encrypt($refresh) : null,
                'expires_at' => $expiresIn ? $now->addSeconds($expiresIn) : null,
                'is_active' => true,
                'connected_at' => $now,
                'disconnected_at' => null,
            ]
        );

        return true;
    }

    /**
     * Obtener token desencriptado
     */
    public function getAccessToken(SocialAccount $account)
    {
        return $account->access_token ? decrypt($account->access_token) : null;
    }

    /**
     * Verificar si un token está expirado
     */
    public function isTokenExpired(SocialAccount $account)
    {
        if (!$account->expires_at) {
            return false; // Si no hay fecha de expiración, asumimos que no expira
        }
        
        return $account->expires_at->isPast();
    }

    /**
     * Refrescar token si es posible
     */
    public function refreshToken(SocialAccount $account)
    {
        if (!$account->refresh_token) {
            return false;
        }

        switch ($account->platform) {
            case 'twitter':
                $resp = Http::asForm()->post('https://api.twitter.com/2/oauth2/token', [
                    'client_id' => config('services.twitter.client_id'),
                    'client_secret' => config('services.twitter.client_secret'),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => decrypt($account->refresh_token),
                ])->json();

                if ($resp['access_token'] ?? null) {
                    $account->update([
                        'access_token' => encrypt($resp['access_token']),
                        'refresh_token' => isset($resp['refresh_token']) ? encrypt($resp['refresh_token']) : $account->refresh_token,
                        'expires_at' => isset($resp['expires_in']) ? now()->addSeconds($resp['expires_in']) : null,
                    ]);
                    return true;
                }
                break;

            case 'reddit':
            $resp = Http::withBasicAuth(config('services.reddit.client_id'), config('services.reddit.client_secret'))
                ->withHeaders(['User-Agent' => 'SocialHubManager/1.0 by YourUsername'])
                ->asForm()
                ->post('https://www.reddit.com/api/v1/access_token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => decrypt($account->refresh_token),
                ])->json();

            if ($resp['access_token'] ?? null) {
                $account->update([
                    'access_token' => encrypt($resp['access_token']),
                    'expires_at' => isset($resp['expires_in']) ? now()->addSeconds($resp['expires_in']) : null,
                ]);
                return true;
            }
            break;
        }

        return false;
    }

    /**
     * Generar code verifier para PKCE (Twitter)
     */
    private function generateCodeVerifier()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * Generar code challenge para PKCE (Twitter)
     */
    private function generateCodeChallenge($codeVerifier)
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }
}