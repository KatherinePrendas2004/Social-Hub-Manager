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

    /**
     * Publicar post instantáneamente
     */
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

    /**
     * Publicar en una plataforma específica
     */
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
                return $this->publishToReddit($post, $accessToken);
            default:
                throw new \Exception("Plataforma no soportada: {$platform}");
        }
    }

    /**
     * Publicar en Twitter
     */
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

    /**
     * Publicar en LinkedIn
     */
    private function publishToLinkedIn(Post $post, SocialAccount $account, string $accessToken)
    {
        try {
            Log::info('Publishing to LinkedIn', ['post_id' => $post->id]);
            
            // 1. Obtener información del usuario usando el endpoint correcto
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

            // 2. Actualizar el platform_user_id si es diferente
            if ($account->platform_user_id !== $userId) {
                $account->update(['platform_user_id' => $userId]);
                Log::info('Updated platform_user_id', ['new_id' => $userId]);
            }

            // 3. Preparar el payload para UGC Posts API
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

            // 4. Publicar usando UGC Posts API
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

            // Si falla, revisar el error específico
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? 'Error desconocido';

            Log::error('LinkedIn API error', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'response' => $response->body()
            ]);

            // Mensajes de error más específicos
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

}