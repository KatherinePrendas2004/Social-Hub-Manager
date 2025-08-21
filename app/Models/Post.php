<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'reddit_title',
        'type',
        'platforms',
        'scheduled_at',
        'status',
        'published_at',
        'publish_results',
    ];

    protected $casts = [
        'platforms' => 'array',
        'publish_results' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la cola de publicaciones
     */
    public function queue(): HasOne
    {
        return $this->hasOne(PostQueue::class);
    }
}