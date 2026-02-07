<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'channel',
        'level',
        'source',
        'message',
        'exception_class',
        'file',
        'line',
        'url',
        'method',
        'user_id',
        'ip_address',
        'user_agent',
        'request_id',
        'fingerprint',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}

