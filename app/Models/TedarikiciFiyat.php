<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TedarikiciFiyat extends Model
{
    protected $table = 'tedarikci_fiyatlari';
    
    protected $fillable = [
        'musteri_id',
        'urun_id',
        'urun_adi',
        'tarih',
        'birim_fiyat',
        'para_birimi',
        'minimum_siparis',
        'temin_suresi',
        'aktif',
        'notlar',
    ];

    protected $casts = [
        'tarih' => 'date',
        'aktif' => 'boolean',
    ];

    public function tedarikci()
    {
        return $this->belongsTo(Musteri::class, 'musteri_id');
    }

    public function urun()
    {
        return $this->belongsTo(Urun::class);
    }
}
