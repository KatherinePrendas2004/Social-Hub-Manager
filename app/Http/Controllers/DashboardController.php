<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\PostQueue;
use App\Models\PublishSchedule;
use App\Services\PublishScheduleService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $scheduleService;

    public function __construct(PublishScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        // Cargar publicaciones pendientes desde PostQueue
        $pendingPosts = PostQueue::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with('post')
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        // Cargar historial de publicaciones (publicadas, fallidas y canceladas)
        $historyPosts = Post::where('user_id', Auth::id())
            ->whereIn('status', ['published', 'failed', 'cancelled'])
            ->latest()
            ->paginate(10, ['*'], 'history_page');

        // Estadísticas básicas
        $stats = [
            'pending' => PostQueue::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'published' => Post::where('user_id', Auth::id())->where('status', 'published')->count(),
            'failed' => Post::where('user_id', Auth::id())->where('status', 'failed')->count(),
            'total' => Post::where('user_id', Auth::id())->count(),
        ];

        return view('posts.index', compact('pendingPosts', 'historyPosts', 'stats'));
    }

    public function analytics()
    {
        try {
            $user = Auth::user();
            
            // Estadísticas básicas
            $basicStats = [
                'pending' => PostQueue::where('user_id', $user->id)->where('status', 'pending')->count(),
                'published' => Post::where('user_id', $user->id)->where('status', 'published')->count(),
                'failed' => Post::where('user_id', $user->id)->where('status', 'failed')->count(),
                'total' => Post::where('user_id', $user->id)->count(),
            ];
            
            // Próximo horario programado - manejo seguro de errores del modelo
            $nextSchedule = null;
            try {
                if (class_exists('App\Models\PublishSchedule')) {
                    // Intentar obtener el próximo horario, pero capturar cualquier error del modelo
                    $nextSchedule = PublishSchedule::getNextScheduleTime($user->id);
                }
            } catch (\Throwable $e) {
                // Capturar cualquier error, incluyendo errores de array keys
                \Log::warning('Error getting next schedule from model: ' . $e->getMessage());
                $nextSchedule = null;
            }
            
            // Estadísticas de publicaciones por mes (últimos 6 meses)
            $monthlyStats = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthlyStats[] = [
                    'month' => $date->format('M Y'),
                    'published' => Post::where('user_id', $user->id)
                        ->where('status', 'published')
                        ->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count(),
                    'failed' => Post::where('user_id', $user->id)
                        ->where('status', 'failed')
                        ->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count(),
                ];
            }
            
            // Estadísticas por plataforma
            $platformStats = [];
            $platforms = ['twitter', 'linkedin', 'reddit'];
            
            foreach ($platforms as $platform) {
                $total = Post::where('user_id', $user->id)
                    ->where('platforms', 'LIKE', '%"' . $platform . '"%')
                    ->count();
                
                $published = Post::where('user_id', $user->id)
                    ->where('status', 'published')
                    ->where('platforms', 'LIKE', '%"' . $platform . '"%')
                    ->count();
                
                if ($total > 0) {
                    $platformStats[$platform] = [
                        'total' => $total,
                        'published' => $published,
                        'success_rate' => round(($published / $total) * 100, 1)
                    ];
                }
            }
            
            // Publicaciones por día de la semana
            $weeklyStats = [];
            $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            
            for ($i = 1; $i <= 7; $i++) {
                $count = Post::where('user_id', $user->id)
                    ->where('status', 'published')
                    ->whereRaw('DAYOFWEEK(created_at) = ?', [$i])
                    ->count();
                
                $weeklyStats[] = [
                    'day' => $days[$i-1],
                    'count' => $count
                ];
            }
            
            // Horarios activos - manejo seguro de errores
            $activeSchedules = [];
            try {
                if (class_exists('App\Models\PublishSchedule')) {
                    $schedules = PublishSchedule::where('user_id', $user->id)
                        ->where('is_active', true)
                        ->orderBy('day_of_week')
                        ->orderBy('time')
                        ->get();

                    // Mapeo manual de días para evitar errores de array keys
                    $dayMapping = [
                        'monday' => 'Lunes',
                        'tuesday' => 'Martes', 
                        'wednesday' => 'Miércoles',
                        'thursday' => 'Jueves',
                        'friday' => 'Viernes',
                        'saturday' => 'Sábado',
                        'sunday' => 'Domingo'
                    ];

                    $activeSchedules = $schedules->map(function($schedule) use ($dayMapping) {
                        return [
                            'day' => $dayMapping[$schedule->day_of_week] ?? ucfirst($schedule->day_of_week),
                            'time' => $schedule->time,
                            'is_active' => $schedule->is_active
                        ];
                    })->toArray();
                }
            } catch (\Throwable $e) {
                \Log::warning('Error getting active schedules: ' . $e->getMessage());
                $activeSchedules = [];
            }

            return response()->json([
                'basic_stats' => $basicStats,
                'next_schedule' => $nextSchedule ? $nextSchedule->format('d/m/Y H:i') : null,
                'monthly_stats' => $monthlyStats,
                'platform_stats' => $platformStats,
                'weekly_stats' => $weeklyStats,
                'active_schedules' => $activeSchedules
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in analytics method: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Error al cargar las analíticas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $queueId)
    {
        try {
            $queuedPost = PostQueue::where('user_id', Auth::id())
                ->where('id', $queueId)
                ->where('status', 'pending')
                ->firstOrFail();

            \DB::transaction(function () use ($queuedPost) {
                // Actualizar el Post asociado a cancelled
                $post = $queuedPost->post;
                $post->update(['status' => 'cancelled']);

                // Eliminar de la cola
                $queuedPost->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Publicación cancelada exitosamente.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cancelar publicación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la publicación: ' . $e->getMessage()
            ], 500);
        }
    }
}