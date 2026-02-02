<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Urun extends Model
{
    protected $table = 'urunler';
    
    protected $fillable = [
        'urun_adi',
        'marka_id',
        'kategori',
        'stok_kodu',
        'son_alis_fiyat',
        'ortalama_kar_orani',
        'notlar',
    ];

    public function marka()
    {
        return $this->belongsTo(Marka::class);
    }

    public function fiyatlar()
    {
        return $this->hasMany(TedarikiciFiyat::class);
    }
}
