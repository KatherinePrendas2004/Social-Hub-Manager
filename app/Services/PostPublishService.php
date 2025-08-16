<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Services\SocialOAuthService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostPublishService
{
    protected $oauthService;

    public function __construct(SocialOAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function publishInstant(Post $post)
    {
        $post->update(['status' => 'publishing']);
        $results = [];

        foreach ($post->platforms as $platform) {
            try {
                $result = $this->publishToPlatform($post, $platform);
                $results[$platform] = $result;
            } catch (\Exception $e) {
                $results[$platform] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'published_at' => null,
                    'platform_post_id' => null,
                ];
                Log::error("Error publicando en {$platform}: " . $e->getMessage(), [
                    'post_id' => $post->id,
                    'platform' => $platform,
                ]);
            }
        }

        // Actualizar post con resultados
        $hasSuccess = collect($results)->some(fn($result) => $result['success']);
        $post->update([
            'status' => $hasSuccess ? 'published' : 'failed',
            'published_at' => $hasSuccess ? now() : null,
            'publish_results' => $results,
        ]);

        return $results;
    }

    private function publishToPlatform(Post $post, string $platform)
    {
        $account = SocialAccount::where('user_id', $post->user_id)
            ->where('platform', $platform)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            throw new \Exception("No hay cuenta conectada para {$platform}");
        }

        // Verificar si el token ha expirado
        if ($this->oauthService->isTokenExpired($account)) {
            if (!$this->oauthService->refreshToken($account)) {
                throw new \Exception("Token expirado para {$platform}. Re-conecta la cuenta.");
            }
        }

        $accessToken = $this->oauthService->getAccessToken($account);

        switch ($platform) {
            case 'twitter':
                return $this->publishToTwitter($post, $accessToken);
            case 'linkedin':
                return $this->publishToLinkedIn($post, $account, $accessToken);
            case 'reddit':
                return $this->publishToReddit($post, $account, $accessToken);
            default:
                throw new \Exception("Plataforma no soportada: {$platform}");
        }
    }

    private function publishToTwitter(Post $post, string $accessToken)
    {
        $payload = ['text' => $post->content];

        $response = Http::withToken($accessToken)
            ->post('https://api.twitter.com/2/tweets', $payload);

        if (!$response->successful()) {
            throw new \Exception('Error en Twitter API: ' . $response->body());
        }

        $data = $response->json();
        return [
            'success' => true,
            'platform_post_id' => $data['data']['id'] ?? null,
            'published_at' => now(),
            'response' => $data,
        ];
    }

    private function publishToLinkedIn(Post $post, SocialAccount $account, string $accessToken)
    {
        try {
            Log::info('Publishing to LinkedIn', ['post_id' => $post->id]);
            
            $userResponse = Http::withToken($accessToken)
                ->get('https://api.linkedin.com/v2/userinfo');

            if (!$userResponse->successful()) {
                Log::error('Failed to get user info', [
                    'status' => $userResponse->status(),
                    'response' => $userResponse->body()
                ]);
                throw new \Exception('No se pudo obtener la información del usuario de LinkedIn. Token inválido o expirado.');
            }

            $userData = $userResponse->json();
            $userId = $userData['sub']; 
            
            Log::info('User info obtained', ['user_id' => $userId]);

            if ($account->platform_user_id !== $userId) {
                $account->update(['platform_user_id' => $userId]);
                Log::info('Updated platform_user_id', ['new_id' => $userId]);
            }

            $payload = [
                'author' => 'urn:li:person:' . $userId,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $post->content
                        ],
                        'shareMediaCategory' => 'NONE'
                    ]
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'
                ]
            ];

            Log::info('Posting to LinkedIn UGC API');

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Restli-Protocol-Version' => '2.0.0'
                ])
                ->timeout(30)
                ->post('https://api.linkedin.com/v2/ugcPosts', $payload);

            if ($response->successful()) {
                $postId = $response->header('X-RestLi-Id');
                
                Log::info('LinkedIn post published successfully', ['post_id' => $postId]);
                
                return [
                    'success' => true,
                    'platform_post_id' => $postId,
                    'published_at' => now(),
                    'response' => $response->json()
                ];
            }

            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? 'Error desconocido';

            Log::error('LinkedIn API error', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'response' => $response->body()
            ]);

            if ($response->status() === 403) {
                if (str_contains($errorMessage, 'w_member_social')) {
                    throw new \Exception('El token no tiene el permiso w_member_social. Reconecta tu cuenta de LinkedIn.');
                }
                throw new \Exception('Sin permisos para publicar en LinkedIn. Reconecta tu cuenta.');
            }

            if ($response->status() === 401) {
                throw new \Exception('Token de LinkedIn expirado o inválido. Reconecta tu cuenta.');
            }

            throw new \Exception('Error al publicar en LinkedIn: ' . $errorMessage);

        } catch (\Exception $e) {
            Log::error('LinkedIn publish failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
                'account_id' => $account->id
            ]);
            
            throw new \Exception('Error de LinkedIn: ' . $e->getMessage());
        }
    }

    private function publishToReddit(Post $post, SocialAccount $account, string $accessToken)
    {
        try {
            Log::info('Intentando publicar en Reddit', [
                'post_id' => $post->id,
                'title' => $post->reddit_title,
                'content' => $post->content,
                'username' => $account->platform_username,
            ]);

            if (!$post->reddit_title) {
                throw new \Exception('Se requiere un título para publicar en Reddit.');
            }

            if (!$account->platform_username) {
                throw new \Exception('No se encontró el nombre de usuario de Reddit. Re-conecta la cuenta.');
            }

            $payload = [
                'kind' => 'self',
                'sr' => 'u_' . $account->platform_username, 
                'title' => $post->reddit_title,
                'text' => $post->content,
            ];

            Log::info('Enviando solicitud a Reddit API', [
                'post_id' => $post->id,
                'payload' => $payload,
            ]);

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'User-Agent' => 'SocialHubManager/1.0 by ' . $account->platform_username,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->asForm()
                ->post('https://oauth.reddit.com/api/submit', $payload);

            Log::info('Respuesta de Reddit API recibida', [
                'post_id' => $post->id,
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                throw new \Exception('Error en Reddit API: ' . $response->body());
            }

            $data = $response->json();

            if (isset($data['json']['errors']) && !empty($data['json']['errors'])) {
                Log::error('Errores en la respuesta de Reddit API', [
                    'post_id' => $post->id,
                    'errors' => $data['json']['errors'],
                ]);
                throw new \Exception('Errores en Reddit API: ' . json_encode($data['json']['errors']));
            }

            if (isset($data['success']) && !$data['success']) {
                throw new \Exception('Reddit API indicó fallo en la publicación');
            }

            $postId = null;
            $postUrl = null;

            if (isset($data['json']['data']['id'])) {
                $postId = $data['json']['data']['id'];
                $postUrl = $data['json']['data']['url'] ?? null;
            }
            elseif (isset($data['jquery']) && is_array($data['jquery'])) {
                foreach ($data['jquery'] as $instruction) {
                    if (is_array($instruction) && count($instruction) >= 3 && 
                        $instruction[1] == 'redirect' || 
                        (isset($instruction[2]) && $instruction[2] == 'call' && 
                        isset($instruction[3]) && is_array($instruction[3]) && 
                        count($instruction[3]) > 0 && strpos($instruction[3][0], 'reddit.com') !== false)) {
                        
                        $redirectUrl = null;
                        if ($instruction[1] == 'redirect' && isset($instruction[2]) && $instruction[2] == 'call') {
                            $redirectUrl = $instruction[3][0] ?? null;
                        } elseif (isset($instruction[3][0])) {
                            $redirectUrl = $instruction[3][0];
                        }
                        
                        if ($redirectUrl && strpos($redirectUrl, 'reddit.com') !== false) {
                            $postUrl = $redirectUrl;
                            if (preg_match('/comments\/([a-zA-Z0-9]+)\//', $redirectUrl, $matches)) {
                                $postId = $matches[1];
                                break;
                            }
                        }
                    }
                }
            }

            if (!$postId) {
                Log::error('No se encontró ID de publicación en la respuesta de Reddit', [
                    'post_id' => $post->id,
                    'response' => $data,
                ]);
                $postId = 'reddit_' . time();
                Log::info('Usando ID temporal para la publicación', ['temp_id' => $postId]);
            }

            Log::info('Publicación en Reddit exitosa', [
                'post_id' => $post->id,
                'platform_post_id' => $postId,
                'post_url' => $postUrl,
            ]);

            return [
                'success' => true,
                'platform_post_id' => $postId,
                'published_at' => now(),
                'response' => $data,
                'extra_info' => [
                    'title' => $post->reddit_title,
                    'url' => $postUrl,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Fallo al publicar en Reddit', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}