<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PublishSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day_of_week',
        'time',
        'is_active',
    ];

    protected $casts = [
        // ❌ QUITA ESTA LÍNEA QUE CAUSA EL ERROR:
        // 'time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    /**
     * Días de la semana en español para mostrar
     */
    public const DAYS_OF_WEEK = [
        'monday' => 'Lunes',
        'tuesday' => 'Martes', 
        'wednesday' => 'Miércoles',
        'thursday' => 'Jueves',
        'friday' => 'Viernes',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo'
    ];

    /**
     * Días de la semana abreviados
     */
    public const DAYS_ABBREVIATED = [
        'monday' => 'L',
        'tuesday' => 'M',
        'wednesday' => 'X',
        'thursday' => 'J',
        'friday' => 'V',
        'saturday' => 'S',
        'sunday' => 'D'
    ];

    /**
     * Orden de los días para mostrar
     */
    public const DAYS_ORDER = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor para obtener el nombre del día en español
     */
    public function getDayNameAttribute(): string
    {
        return self::DAYS_OF_WEEK[$this->day_of_week] ?? $this->day_of_week;
    }

    /**
     * Accessor para obtener el día abreviado
     */
    public function getDayAbbreviatedAttribute(): string
    {
        return self::DAYS_ABBREVIATED[$this->day_of_week] ?? substr($this->day_of_week, 0, 1);
    }

    /**
     * Accessor para obtener la hora formateada
     * ✅ MEJORADO: Maneja mejor el formato de hora
     */
    public function getTimeFormattedAttribute(): string
    {
        // Si ya viene en formato HH:MM, simplemente devolver
        if (preg_match('/^\d{2}:\d{2}$/', $this->time)) {
            return $this->time;
        }
        
        // Si viene con segundos (HH:MM:SS), quitar los segundos
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $this->time)) {
            return substr($this->time, 0, 5);
        }
        
        // Como fallback, intentar parsear con Carbon
        try {
            return Carbon::parse($this->time)->format('H:i');
        } catch (\Exception $e) {
            return $this->time; // Devolver el valor original si no se puede parsear
        }
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar solo horarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por día de la semana
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Obtener horarios agrupados por día para un usuario
     */
    public static function getScheduleGridForUser($userId)
    {
        $schedules = self::forUser($userId)
            ->active()
            ->orderBy('time')
            ->get()
            ->groupBy('day_of_week');

        $grid = [];
        foreach (self::DAYS_ORDER as $day) {
            $grid[$day] = $schedules->get($day, collect())->pluck('time_formatted')->toArray();
        }

        return $grid;
    }

    /**
     * Obtener el próximo horario de publicación
     */
    public static function getNextScheduleTime($userId)
    {
        $now = now();
        $currentDay = strtolower($now->format('l')); // monday, tuesday, etc.
        $currentTime = $now->format('H:i');

        // Buscar hoy después de la hora actual
        $todaySchedule = self::forUser($userId)
            ->active()
            ->forDay($currentDay)
            ->whereTime('time', '>', $currentTime)
            ->orderBy('time')
            ->first();

        if ($todaySchedule) {
            return $now->copy()->setTimeFromTimeString($todaySchedule->time_formatted);
        }

        // Buscar en los próximos días
        $daysToCheck = self::DAYS_ORDER;
        $currentDayIndex = array_search($currentDay, $daysToCheck);
        
        // Reordenar array para empezar desde mañana
        $futureDays = array_slice($daysToCheck, $currentDayIndex + 1);
        $pastDays = array_slice($daysToCheck, 0, $currentDayIndex + 1);
        $orderedDays = array_merge($futureDays, $pastDays);

        foreach ($orderedDays as $day) {
            $schedule = self::forUser($userId)
                ->active()
                ->forDay($day)
                ->orderBy('time')
                ->first();

            if ($schedule) {
                $nextWeek = $day <= $currentDay;
                $targetDate = $now->copy()->next($day);
                if ($nextWeek && $day !== $currentDay) {
                    $targetDate = $now->copy()->next($day);
                } elseif ($day === $currentDay) {
                    $targetDate = $now->copy()->addWeek()->next($day);
                }
                
                return $targetDate->setTimeFromTimeString($schedule->time_formatted);
            }
        }

        return null; // No hay horarios configurados
    }

    /**
     * Verificar si hay horarios configurados para un usuario
     */
    public static function hasSchedulesForUser($userId)
    {
        return self::forUser($userId)->active()->exists();
    }
}