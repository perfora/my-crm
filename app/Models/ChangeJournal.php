<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeJournal extends Model
{
    protected $fillable = [
        'task_key',
        'attempt_no',
        'actor',
        'status',
        'summary',
        'commit_hash',
        'user_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}

