<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HhAccount extends Model
{
    protected $fillable = [
        'user_id',
        'hh_user_id',
        'token_type',
        'access_token',
        'refresh_token',
        'expires_at',
        'profile',
        'token_payload',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'profile'      => 'array',
        'token_payload'=> 'array',
        'access_token'   => 'encrypted',
        'refresh_token'  => 'encrypted',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
