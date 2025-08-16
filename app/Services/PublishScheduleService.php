<?php

namespace App\Services;

use App\Models\PublishSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishScheduleService
{
    public function getUserSchedules(User $user)
    {
        return PublishSchedule::where('user_id', $user->id)->get();
    }

    public function createSchedule(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            return PublishSchedule::create([
                'user_id' => $user->id,
                'day_of_week' => $data['day_of_week'],
                'time' => $data['time'],
                'is_active' => true,
            ]);
        });
    }

    public function getScheduleById($id, User $user)
    {
        $schedule = PublishSchedule::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        return $schedule;
    }

    public function updateSchedule($id, User $user, array $data)
    {
        return DB::transaction(function () use ($id, $user, $data) {
            $schedule = $this->getScheduleById($id, $user);
            $schedule->update([
                'day_of_week' => $data['day_of_week'],
                'time' => $data['time'],
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $schedule->is_active,
            ]);
            return $schedule;
        });
    }

    public function deleteSchedule($id, User $user)
    {
        return DB::transaction(function () use ($id, $user) {
            $schedule = $this->getScheduleById($id, $user);
            $schedule->delete();
        });
    }

    public function toggleSchedule($id, User $user)
    {
        return DB::transaction(function () use ($id, $user) {
            $schedule = $this->getScheduleById($id, $user);
            $schedule->is_active = !$schedule->is_active;
            $schedule->save();
            return $schedule;
        });
    }

    public function cloneDaySchedules(User $user, $fromDay, $toDay)
    {
        return DB::transaction(function () use ($user, $fromDay, $toDay) {
            $schedules = PublishSchedule::where('user_id', $user->id)
                ->where('day_of_week', $fromDay)
                ->get();

            foreach ($schedules as $schedule) {
                PublishSchedule::create([
                    'user_id' => $user->id,
                    'day_of_week' => $toDay,
                    'time' => $schedule->time,
                    'is_active' => $schedule->is_active,
                ]);
            }
        });
    }

    public function getScheduleStats(User $user)
    {
        $total = PublishSchedule::where('user_id', $user->id)->count();
        $active = PublishSchedule::where('user_id', $user->id)->where('is_active', true)->count();
        $weeklyPosts = $total * 1; // Asumiendo 1 publicación por horario por semana

        return [
            'total' => $total,
            'active' => $active,
            'total_weekly_posts' => $weeklyPosts,
        ];
    }

    /**
     * ✅ MÉTODO CORREGIDO: Maneja correctamente el formato TIME de MySQL
     */
    /**
 * 🚨 REEMPLAZA ESTE MÉTODO EN PublishScheduleService AHORA MISMO
 */
public function getNextSchedule(User $user)
{
    $now = Carbon::now();
    $nowLocal = Carbon::now('America/Costa_Rica'); // Tu timezone
    
    \Log::info('🚨 DEBUG TIMEZONE', [
        'now_utc' => $now->format('Y-m-d H:i:s'),
        'now_local' => $nowLocal->format('Y-m-d H:i:s'),
        'timezone_config' => config('app.timezone'),
        'carbon_default_timezone' => Carbon::now()->timezoneName,
        'php_timezone' => date_default_timezone_get(),
        'server_time' => date('Y-m-d H:i:s'),
    ]);
    
    // Usar la hora local de Costa Rica
    $workingNow = $nowLocal;
    
    \Log::info('🔍 USANDO HORA LOCAL', [
        'working_now' => $workingNow->format('Y-m-d H:i:s'),
        'day' => strtolower($workingNow->format('l')),
        'user_id' => $user->id
    ]);

    $schedules = PublishSchedule::where('user_id', $user->id)
        ->where('is_active', true)
        ->get();

    if ($schedules->isEmpty()) {
        \Log::warning('❌ NO HAY HORARIOS ACTIVOS');
        return null;
    }

    \Log::info('📋 HORARIOS ENCONTRADOS:', $schedules->map(fn($s) => [
        'day' => $s->day_of_week,
        'time' => $s->time,
        'formatted' => $s->time_formatted
    ])->toArray());

    // ✅ BUSCAR HOY con la hora local
    $today = strtolower($workingNow->format('l'));
    $currentTime = $workingNow->format('H:i');
    
    \Log::info('🕐 COMPARACIÓN DE TIEMPO', [
        'today' => $today,
        'current_time' => $currentTime,
        'looking_for_day' => $today
    ]);
    
    $todaySchedules = $schedules->where('day_of_week', $today);
    
    foreach ($todaySchedules as $schedule) {
        $scheduleTime = $schedule->time_formatted;
        
        \Log::info('⏰ COMPARANDO HORARIOS', [
            'schedule_time' => $scheduleTime,
            'current_time' => $currentTime,
            'is_future' => $scheduleTime > $currentTime ? 'SÍ' : 'NO'
        ]);
        
        if ($scheduleTime > $currentTime) {
            $candidateDateTime = $workingNow->copy()
                ->setHour((int) substr($scheduleTime, 0, 2))
                ->setMinute((int) substr($scheduleTime, 3, 2))
                ->setSecond(0);
            
            \Log::info('✅ HORARIO HOY ENCONTRADO', [
                'candidate' => $candidateDateTime->format('Y-m-d H:i:s'),
                'schedule_time' => $scheduleTime
            ]);
            
            return $candidateDateTime;
        }
    }
    
    // Si no encuentra hoy, buscar próximos días...
    \Log::info('❌ NO HAY HORARIOS HOY, BUSCANDO PRÓXIMOS DÍAS...');
    
    for ($daysAhead = 1; $daysAhead <= 7; $daysAhead++) {
        $targetDate = $workingNow->copy()->addDays($daysAhead);
        $targetDay = strtolower($targetDate->format('l'));
        
        $daySchedules = $schedules->where('day_of_week', $targetDay);
        
        foreach ($daySchedules as $schedule) {
            $scheduleTime = $schedule->time_formatted;
            
            $candidateDateTime = $targetDate->copy()
                ->setHour((int) substr($scheduleTime, 0, 2))
                ->setMinute((int) substr($scheduleTime, 3, 2))
                ->setSecond(0);
            
            \Log::info('✅ PRÓXIMO DÍA ENCONTRADO', [
                'days_ahead' => $daysAhead,
                'target_day' => $targetDay,
                'candidate' => $candidateDateTime->format('Y-m-d H:i:s')
            ]);
            
            return $candidateDateTime;
        }
    }
    
    \Log::warning('❌ NO SE ENCONTRÓ NINGÚN HORARIO');
    return null;
}

    /**
     * ✅ HELPER: Parse del tiempo desde la DB
     */
    private function parseScheduleTime($timeValue)
    {
        // Si ya está en formato HH:MM, devolver tal como está
        if (preg_match('/^\d{2}:\d{2}$/', $timeValue)) {
            return $timeValue;
        }
        
        // Si está en formato HH:MM:SS, quitar los segundos
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $timeValue)) {
            return substr($timeValue, 0, 5);
        }
        
        // Intentar parsear con Carbon como fallback
        try {
            return Carbon::parse($timeValue)->format('H:i');
        } catch (\Exception $e) {
            Log::error('Error parseando hora', [
                'time_value' => $timeValue,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }


    public function getSuggestedTimes()
    {
        return [
            'morning' => ['08:00', '09:00', '10:00'],
            'afternoon' => ['12:00', '14:00', '15:00'],
            'evening' => ['18:00', '19:00', '20:00'],
        ];
    }

    public function formatScheduleDataForCalendar($schedules)
    {
        $scheduleData = [];

        foreach (array_keys(PublishSchedule::DAYS_OF_WEEK) as $day) {
            $scheduleData[$day] = [];
            $daySchedules = $schedules->where('day_of_week', $day);

            foreach ($daySchedules as $schedule) {
                $scheduleData[$day][] = [
                    'id' => $schedule->id,
                    // ✅ FIX: Usar time_formatted del modelo
                    'time' => $schedule->time_formatted,
                    'is_active' => $schedule->is_active,
                ];
            }

            usort($scheduleData[$day], function ($a, $b) {
                return strcmp($a['time'], $b['time']);
            });
        }

        return $scheduleData;
    }
}