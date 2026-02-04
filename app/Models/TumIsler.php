<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TumIsler extends Model
{
    protected $table = 'tum_isler';
    
    protected $fillable = [
        'name',
        'musteri_id',
        'marka_id',
        'tipi',
        'turu',
        'oncelik',
        'kaybedilme_nedeni',
        'register_durum',
        'teklif_tutari',
        'teklif_doviz',
        'teklif_tutari_orj',
        'alis_tutari',
        'alis_doviz',
        'alis_tutari_orj',
        'kur',
        'orj_kur',
        'kapanis_tarihi',
        'lisans_bitis',
        'is_guncellenme_tarihi',
        'notlar',
        'gecmis_notlar',
        'aciklama',
        'notion_id',
        'notion_url',
    ];

    protected $casts = [
        'teklif_tutari' => 'decimal:2',
        'teklif_tutari_orj' => 'decimal:2',
        'alis_tutari' => 'decimal:2',
        'alis_tutari_orj' => 'decimal:2',
        'kur' => 'decimal:4',
        'orj_kur' => 'decimal:4',
        'kapanis_tarihi' => 'date',
        'lisans_bitis' => 'date',
        'is_guncellenme_tarihi' => 'date',
    ];

    // İlişkiler
    public function musteri()
    {
        return $this->belongsTo(Musteri::class);
    }

    public function marka()
    {
        return $this->belongsTo(Marka::class);
    }

    // Hesaplanmış alanlar
    public function getKarTutariAttribute()
    {
        if ($this->teklif_tutari && $this->alis_tutari) {
            return $this->teklif_tutari - $this->alis_tutari;
        }
        return null;
    }

    public function getKarOraniAttribute()
    {
        if ($this->teklif_tutari && $this->alis_tutari && $this->alis_tutari > 0) {
            // Kar oranı alış üzerinden hesaplanır
            return (($this->teklif_tutari - $this->alis_tutari) / $this->alis_tutari) * 100;
        }
        return null;
    }

    // Durum formülü (Notion'daki gibi)
    public function getDurumAttribute()
    {
        if ($this->tipi == 'Kazanıldı') {
            if ($this->turu == 'Destek') {
                return '🟢 ↑ OK';
            }
            
            if (empty($this->alis_tutari)) {
                return '🔴 Alış tutarı gir';
            }
            
            return '🟢 ↑ OK';
        }
        
        return '';
    }
}