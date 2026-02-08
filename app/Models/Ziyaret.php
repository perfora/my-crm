<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ziyaret extends Model
{
    protected $table = 'ziyaretler';
    
    protected $fillable = [
        'ziyaret_ismi',
        'musteri_id',
        'ziyaret_tarihi',
        'arama_tarihi',
        'tur',
        'durumu',
        'ziyaret_notlari',
        'ews_item_id',
        'ews_change_key',
    ];

    protected $casts = [
        'ziyaret_tarihi' => 'datetime',
        'arama_tarihi' => 'datetime',
    ];

    // İlişki
    public function musteri()
    {
        return $this->belongsTo(Musteri::class);
    }

    // Durum-Tür formülü (Notion'daki gibi)
    public function getDurumTurAttribute()
    {
        $parts = [];
        
        if ($this->durumu) {
            $parts[] = $this->durumu;
        }
        
        if ($this->tur) {
            $parts[] = $this->tur;
        }
        
        return !empty($parts) ? implode(' - ', $parts) : null;
    }

    // Takvim Tarihi formülü
    public function getTakvimTarihiAttribute()
    {
        if ($this->ziyaret_tarihi) {
            return $this->ziyaret_tarihi->format('d.m.Y H:i');
        }
        
        if ($this->arama_tarihi) {
            return $this->arama_tarihi->format('d.m.Y H:i');
        }
        
        return null;
    }
}
