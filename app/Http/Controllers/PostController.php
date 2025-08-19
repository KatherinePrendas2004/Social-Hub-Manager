<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostQueue;
use App\Models\SocialAccount;
use App\Http\Requests\StorePostRequest;
use App\Services\PostPublishService;
use App\Services\PublishScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PostController extends Controller
{
    protected $publishService;
    protected $scheduleService;

    public function __construct(PostPublishService $publishService, PublishScheduleService $scheduleService)
    {
        $this->publishService = $publishService;
        $this->scheduleService = $scheduleService;
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
            'reddit_title' => $validated['reddit_title'] ?? null,
            'type' => $validated['type'],
            'platforms' => $validated['platforms'],
            'scheduled_at' => $validated['type'] === 'scheduled' ? $validated['scheduled_at'] : null,
            'status' => $validated['type'] === 'instant' ? 'draft' : 'queued',
        ]);

        // Manejar según el tipo de publicación
        if ($validated['type'] === 'instant') {
            try {
                $results = $this->publishService->publishInstant($post);
                
                $successful = collect($results)->filter(fn($result) => $result['success']);
                $failed = collect($results)->filter(fn($result) => !$result['success']);
                
                $messages = [];
                
                foreach ($successful as $platform => $result) {
                    $platformName = ucfirst($platform);
                    $extraInfo = '';
                    
                    if (isset($result['extra_info'])) {
                        switch ($platform) {
                            case 'reddit':
                                $extraInfo = " con título: {$result['extra_info']['title']}";
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
                
                if ($successful->count() > 0 && $failed->count() === 0) {
                    $message = '¡Publicación enviada exitosamente a todas las redes!' . "\n\n" . implode("\n", $messages);
                    return redirect()->route('dashboard.index')->with('success', $message);
                } elseif ($successful->count() > 0 && $failed->count() > 0) {
                    $message = 'Publicación enviada parcialmente. Algunos errores ocurrieron:' . "\n\n" . implode("\n", $messages);
                    return redirect()->route('dashboard.index')->with('warning', $message);
                } else {
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
        } elseif ($validated['type'] === 'queued') {
            
            // 🚨 AQUÍ ESTÁ EL PROBLEMA - AÑADIR DEBUG
            \Log::info('🔍 INICIANDO BÚSQUEDA DE PRÓXIMO HORARIO', [
                'user_id' => Auth::id(),
                'current_time' => now()->format('Y-m-d H:i:s'),
                'post_id' => $post->id
            ]);
            
            // Obtener el próximo horario disponible
            $nextSchedule = $this->scheduleService->getNextSchedule(Auth::user());
            
            \Log::info('📅 RESULTADO DE getNextSchedule', [
                'next_schedule' => $nextSchedule ? $nextSchedule->format('Y-m-d H:i:s') : 'NULL',
                'user_id' => Auth::id(),
                'post_id' => $post->id
            ]);
            
            if (!$nextSchedule) {
                $post->delete(); // Eliminar el post si no hay horarios
                return redirect()->route('dashboard.index')
                    ->with('error', 'No hay horarios activos configurados. Por favor, configura un horario antes de programar una publicación en cola.');
            }

            // 🚨 AQUÍ SE GUARDA EN LA COLA - AÑADIR MÁS DEBUG
            \Log::info('💾 GUARDANDO EN PostQueue', [
                'post_id' => $post->id,
                'user_id' => Auth::id(),
                'scheduled_at' => $nextSchedule->format('Y-m-d H:i:s'),
                'scheduled_at_raw' => $nextSchedule
            ]);

            // Crear entrada en la cola
            $queuedPost = PostQueue::create([
                'post_id' => $post->id,
                'user_id' => Auth::id(),
                'scheduled_at' => $nextSchedule,
                'status' => 'pending',
            ]);
            
            \Log::info('✅ PostQueue CREADO', [
                'queue_id' => $queuedPost->id,
                'scheduled_at_saved' => $queuedPost->scheduled_at->format('Y-m-d H:i:s'),
                'post_id' => $post->id
            ]);

            return redirect()->route('dashboard.index')
                ->with('success', 'Publicación añadida a la cola para el ' . $nextSchedule->format('d/m/Y H:i'));
        } elseif ($validated['type'] === 'scheduled') {
            // Convertir scheduled_at a Carbon con zona horaria de Costa Rica
            $scheduledAt = Carbon::parse($validated['scheduled_at'])->setTimezone(config('app.timezone', 'America/Costa_Rica'));

            // Crear el post
            $post = Post::create([
                'user_id' => Auth::id(),
                'content' => $validated['content'],
                'reddit_title' => $validated['reddit_title'] ?? null,
                'type' => $validated['type'],
                'platforms' => $validated['platforms'],
                'scheduled_at' => $scheduledAt,
                'status' => 'queued', // Cambiado a 'queued' para que aparezca en la cola
            ]);

            // Agregar a la cola con scheduled_at específico
            \Log::info('💾 GUARDANDO PUBLICACIÓN PROGRAMADA EN PostQueue', [
                'post_id' => $post->id,
                'user_id' => Auth::id(),
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            ]);

            $queuedPost = PostQueue::create([
                'post_id' => $post->id,
                'user_id' => Auth::id(),
                'scheduled_at' => $scheduledAt,
                'status' => 'pending',
            ]);

            \Log::info('✅ PostQueue CREADO PARA PROGRAMADA', [
                'queue_id' => $queuedPost->id,
                'scheduled_at_saved' => $queuedPost->scheduled_at->format('Y-m-d H:i:s'),
                'post_id' => $post->id
            ]);

            return redirect()->route('dashboard.index')
                ->with('success', 'Publicación programada para el ' . $scheduledAt->format('d/m/Y H:i') . '. Aparecerá en la cola.');
        }
    }
}