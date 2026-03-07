<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    protected $fillable = [
        'title',
        'analysis_type',
        'source_page',
        'prompt_key',
        'prompt_version',
        'request_payload',
        'response_text',
        'response_meta',
        'status',
        'requested_by',
        'error_message',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->analysis_type) {
            'dashboard' => 'Dashboard Analizi',
            'ziyaret' => 'Ziyaret Analizi',
            default => ucfirst($this->analysis_type),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Bekliyor',
            'completed' => 'Tamamlandı',
            'failed' => 'Hata',
            default => ucfirst($this->status),
        };
    }
}
