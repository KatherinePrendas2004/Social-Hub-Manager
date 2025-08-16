<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Http\Requests\StorePostRequest;
use App\Services\PostPublishService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected $publishService;

    public function __construct(PostPublishService $publishService)
    {
        $this->publishService = $publishService;
    }

    /**
     * Mostrar formulario de nueva publicación
     */
    public function create()
    {
        $connectedAccounts = Auth::user()->socialAccounts()
            ->where('is_active', true)
            ->get()
            ->pluck('platform')
            ->toArray();

        if (empty($connectedAccounts)) {
            return redirect()->route('social.index')
                ->with('error', 'Debes conectar al menos una red social antes de publicar.');
        }

        return view('posts.create', compact('connectedAccounts'));
    }

    /**
     * Almacenar nueva publicación
     */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();
        
        // Crear el post
        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'type' => $validated['type'],
            'platforms' => $validated['platforms'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => 'draft',
        ]);

        // Si es publicación instantánea, publicar ahora
        if ($validated['type'] === 'instant') {
            try {
                $results = $this->publishService->publishInstant($post);
                
                $successful = collect($results)->filter(fn($result) => $result['success']);
                $failed = collect($results)->filter(fn($result) => !$result['success']);
                
                // Crear mensajes detallados
                $messages = [];
                
                foreach ($successful as $platform => $result) {
                    $platformName = ucfirst($platform);
                    $extraInfo = '';
                    
                    // Información adicional específica por plataforma
                    if (isset($result['extra_info'])) {
                        switch ($platform) {
                            case 'reddit':
                                $extraInfo = " en r/{$result['extra_info']['subreddit']}";
                                break;
                        }
                    }
                    
                    $messages[] = "✅ {$platformName}{$extraInfo}: Publicado exitosamente";
                }
                
                foreach ($failed as $platform => $result) {
                    $platformName = ucfirst($platform);
                    $error = $result['error'] ?? 'Error desconocido';
                    $messages[] = "❌ {$platformName}: {$error}";
                }
                
                // Determinar el tipo de mensaje y redirección
                if ($successful->count() > 0 && $failed->count() === 0) {
                    // Éxito total
                    $message = '¡Publicación enviada exitosamente a todas las redes!' . "\n\n" . implode("\n", $messages);
                    return redirect()->route('dashboard.index')->with('success', $message);
                    
                } elseif ($successful->count() > 0 && $failed->count() > 0) {
                    // Éxito parcial
                    $message = 'Publicación enviada parcialmente. Algunos errores ocurrieron:' . "\n\n" . implode("\n", $messages);
                    return redirect()->route('dashboard.index')->with('warning', $message);
                    
                } else {
                    // Fallo total
                    $message = 'No se pudo publicar en ninguna red social:' . "\n\n" . implode("\n", $messages);
                    return redirect()->route('dashboard.index')->with('error', $message);
                }
                
            } catch (\Exception $e) {
                \Log::error('Error general en publicación', [
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);
                
                return redirect()->route('dashboard.index')
                    ->with('error', 'Error inesperado al procesar la publicación: ' . $e->getMessage());
            }
        }

        // Para tipos queued y scheduled (no implementados aún)
        return redirect()->route('dashboard.index')
            ->with('info', 'Publicación guardada. Las funciones de cola y programación estarán disponibles próximamente.');
    }

}