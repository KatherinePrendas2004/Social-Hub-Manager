<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'media',
        'type',
        'status',
        'platforms',
        'scheduled_at',
        'published_at',
        'publish_results',
    ];

    protected $casts = [
        'media' => 'array',
        'platforms' => 'array',
        'publish_results' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeInstant($query)
    {
        return $query->where('type', 'instant');
    }

    public function scopeQueued($query)
    {
        return $query->where('type', 'queued');
    }

    public function scopeScheduled($query)
    {
        return $query->where('type', 'scheduled');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'pending']);
    }

    // Accessors
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getFormattedScheduledAtAttribute()
    {
        return $this->scheduled_at ? $this->scheduled_at->format('d/m/Y H:i') : null;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Borrador'],
            'pending' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Pendiente'],
            'publishing' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Publicando'],
            'published' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Publicado'],
            'failed' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Falló'],
            'cancelled' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Cancelado'],
        ];

        return $badges[$this->status] ?? $badges['draft'];
    }

    public function getPlatformIconsAttribute()
    {
        $icons = [
            'twitter' => 'twitter',
            'linkedin' => 'linkedin',
            'reddit' => 'disc',
        ];

        return collect($this->platforms)->map(fn($platform) => [
            'name' => $platform,
            'icon' => $icons[$platform] ?? 'globe',
        ]);
    }

    // Métodos de utilidad
    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['draft', 'failed']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'publishing']);
    }
}