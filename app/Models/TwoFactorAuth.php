<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwoFactorAuth extends Model
{
    use HasFactory;

    protected $table = 'two_factor_auths';

    protected $fillable = [
        'user_id',
        'secret',
        'recovery_codes',
        'enabled_at',
        'last_used_at',
    ];

    protected $casts = [
        'recovery_codes' => 'array',
        'enabled_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}