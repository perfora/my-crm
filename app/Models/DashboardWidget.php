<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    protected $fillable = ['dashboard_id', 'type', 'data_source', 'columns', 'filters', 'config', 'order'];
    protected $casts = ['columns' => 'array', 'filters' => 'array', 'config' => 'array'];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }
}
