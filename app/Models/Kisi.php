<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kisi extends Model
{
    protected $table = 'kisiler';
    
    protected $fillable = [
        'ad_soyad',
        'telefon_numarasi',
        'email_adresi',
        'bolum',
        'gorev',
        'musteri_id',
        'url'
    ];

    // İlişki
    public function musteri()
    {
        return $this->belongsTo(Musteri::class);
    }
}