<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marka extends Model
{
    protected $table = 'markalar';
    
    protected $fillable = [
        'name',
        'notion_id',
        'notion_url',
    ];

    public function tumIsler()
    {
        return $this->hasMany(TumIsler::class);
    }
}