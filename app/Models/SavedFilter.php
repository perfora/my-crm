<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedFilter extends Model
{
    protected $fillable = ['name', 'page', 'filter_data'];
    
    protected $casts = [
        'filter_data' => 'array',
    ];
}
