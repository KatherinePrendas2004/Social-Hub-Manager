<?php

namespace App\Console\Commands;

use App\Models\PostQueue;
use App\Services\PostPublishService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessQueuedPosts extends Command
{
    protected $signature = 'posts:process-queue {--dry-run : Mostrar qué se procesaría sin ejecutar}';
    protected $description = 'Procesa las publicaciones en cola que han alcanzado su horario programado';

    protected $publishService;

    public function __construct(PostPublishService $publishService)
    {
        parent::__construct();
        $this->publishService = $publishService;
    }

    public function handle()
    {
        $currentTime = Carbon::now(config('app.timezone', 'America/Costa_Rica'));
        
        $this->info("🕐 Procesando cola a las: {$currentTime->format('Y-m-d H:i:s')} ({$currentTime->timezoneName})");
        
        // ✅ USAR EL NUEVO SCOPE MÁS ESTRICTO
        $queuedPosts = PostQueue::pending()
            ->dueNow() // En lugar de ->due()
            ->with(['post', 'user'])
            ->orderBy('scheduled_at')
            ->get();

        if ($queuedPosts->isEmpty()) {
            $this->info('✅ No hay publicaciones en cola para procesar ahora.');
            
            // 📊 MOSTRAR PRÓXIMAS PUBLICACIONES
            $upcomingPosts = PostQueue::pending()
                ->with('post')
                ->orderBy('scheduled_at')
                ->take(3)
                ->get();
                
            if ($upcomingPosts->isNotEmpty()) {
                $this->info("\n📅 Próximas publicaciones programadas:");
                foreach ($upcomingPosts as $upcoming) {
                    $scheduledTime = $upcoming->scheduled_at->setTimezone(config('app.timezone', 'America/Costa_Rica'));
                    $minutesUntil = $upcoming->getTimeUntilPublication();
                    $this->line("   • Post #{$upcoming->post_id} - {$scheduledTime->format('d/m/Y H:i')} (en {$minutesUntil} min)");
                }
            }
            return;
        }

        $this->info("📋 Encontradas {$queuedPosts->count()} publicaciones para procesar:");
        
        foreach ($queuedPosts as $queuedPost) {
            $scheduledTime = $queuedPost->scheduled_at->setTimezone(config('app.timezone', 'America/Costa_Rica'));
            $delay = $currentTime->diffInMinutes($queuedPost->scheduled_at);
            
            $this->line("   • Post #{$queuedPost->post_id} - Programado: {$scheduledTime->format('H:i')} (retraso: {$delay} min)");
            
            // 🧪 MODO DRY-RUN para testing
            if ($this->option('dry-run')) {
                $this->warn("   [DRY-RUN] Se procesaría ahora...");
                continue;
            }
            
            if (!$queuedPost->isReadyToPublish()) {
                $this->warn("   ⏰ Post #{$queuedPost->post_id} aún no está listo. Saltando...");
                continue;
            }
            
            try {
                $this->info("   🚀 Procesando Post #{$queuedPost->post_id}...");
                
                $queuedPost->update(['status' => 'processing']);
                
                // 📝 LOG DETALLADO
                Log::info('📤 PROCESANDO PUBLICACIÓN EN COLA', [
                    'post_id' => $queuedPost->post_id,
                    'user_id' => $queuedPost->user_id,
                    'scheduled_at' => $queuedPost->scheduled_at->format('Y-m-d H:i:s'),
                    'current_time' => $currentTime->format('Y-m-d H:i:s'),
                    'delay_minutes' => $delay,
                    'content_preview' => substr($queuedPost->post->content, 0, 100) . '...'
                ]);

                $results = $this->publishService->publishInstant($queuedPost->post);

                $successful = collect($results)->filter(function ($result) {
                    return isset($result['success']) && $result['success'];
                });
                $failed = collect($results)->filter(function ($result) {
                    return !isset($result['success']) || !$result['success'];
                });

                if ($successful->count() > 0) {
                    $queuedPost->update(['status' => 'published']);
                    $queuedPost->post->update(['status' => 'published']);
                    
                    $platforms = $successful->keys()->implode(', ');
                    $this->info("   ✅ Post #{$queuedPost->post_id} publicado en: {$platforms}");
                    
                    Log::info('✅ PUBLICACIÓN EXITOSA', [
                        'post_id' => $queuedPost->post_id,
                        'platforms' => $platforms,
                        'delay_minutes' => $delay
                    ]);
                } else {
                    $errorMessages = $failed->map(function ($result, $platform) {
                        $error = isset($result['error']) ? $result['error'] : 'Error desconocido';
                        return "{$platform}: {$error}";
                    })->implode(', ');
                    
                    $queuedPost->update([
                        'status' => 'failed',
                        'error_message' => $errorMessages,
                    ]);
                    
                    $this->error("   ❌ Error en Post #{$queuedPost->post_id}: {$errorMessages}");
                    
                    Log::error('❌ ERROR EN PUBLICACIÓN', [
                        'post_id' => $queuedPost->post_id,
                        'errors' => $errorMessages,
                        'delay_minutes' => $delay
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('💥 EXCEPCIÓN EN COLA', [
                    'post_id' => $queuedPost->post_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $queuedPost->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                
                $this->error("   💥 Excepción en Post #{$queuedPost->post_id}: {$e->getMessage()}");
            }
        }

        $this->info("\n🏁 Procesamiento de publicaciones en cola completado.");
    }
}