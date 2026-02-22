<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'ai_api_token_id',
        'user_id',
        'channel',
        'action',
        'http_method',
        'route',
        'url',
        'ip_address',
        'user_agent',
        'status_code',
        'duration_ms',
        'request_query',
        'meta',
    ];

    protected $casts = [
        'request_query' => 'array',
        'meta' => 'array',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(AiApiToken::class, 'ai_api_token_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

