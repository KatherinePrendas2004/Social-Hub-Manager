<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PostQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'scheduled_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el post
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para publicaciones en cola pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * ✅ SCOPE CORREGIDO: Respeta la zona horaria de Costa Rica
     */
    public function scopeDue($query)
    {
        // Usar la zona horaria configurada en la aplicación
        $now = Carbon::now(config('app.timezone', 'America/Costa_Rica'));
        
        \Log::info('🔍 VERIFICANDO PUBLICACIONES VENCIDAS', [
            'current_time_costa_rica' => $now->format('Y-m-d H:i:s'),
            'timezone' => $now->timezoneName,
            'utc_time' => Carbon::now('UTC')->format('Y-m-d H:i:s')
        ]);
        
        return $query->where('scheduled_at', '<=', $now);
    }

    /**
     * ✅ NUEVO: Scope más estricto para publicaciones que deben ejecutarse AHORA
     */
    public function scopeDueNow($query)
    {
        $now = Carbon::now(config('app.timezone', 'America/Costa_Rica'));
        
        // Solo publicaciones que deberían haberse ejecutado en los últimos 2 minutos
        // Esto evita publicar cosas muy antiguas por error
        $twoMinutesAgo = $now->copy()->subMinutes(2);
        
        return $query->where('scheduled_at', '<=', $now)
                    ->where('scheduled_at', '>=', $twoMinutesAgo);
    }

    /**
     * ✅ NUEVO: Verificar si está realmente listo para publicar
     */
    public function isReadyToPublish(): bool
    {
        $now = Carbon::now(config('app.timezone', 'America/Costa_Rica'));
        return $this->scheduled_at <= $now;
    }

    /**
     * ✅ NUEVO: Obtener tiempo restante hasta la publicación
     */
    public function getTimeUntilPublication(): ?int
    {
        $now = Carbon::now(config('app.timezone', 'America/Costa_Rica'));
        
        if ($this->scheduled_at <= $now) {
            return 0; // Ya es hora o se pasó
        }
        
        return $this->scheduled_at->diffInMinutes($now);
    }
}