<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oncelik extends Model
{
    protected $table = 'oncelikler';
    protected $fillable = ['name', 'sira'];
}
