<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeklifKalem extends Model
{
    protected $table = 'teklif_kalemleri';
    
    protected $fillable = [
        'teklif_id',
        'musteri_id',
        'urun_id',
        'sira',
        'urun_adi',
        'alis_fiyat',
        'adet',
        'alis_toplam',
        'kar_orani',
        'satis_fiyat',
        'satis_toplam',
        'para_birimi',
        'notlar',
    ];

    public function teklif()
    {
        return $this->belongsTo(FiyatTeklif::class);
    }

    public function tedarikci()
    {
        return $this->belongsTo(Musteri::class, 'musteri_id');
    }

    public function urun()
    {
        return $this->belongsTo(Urun::class);
    }

    public function hesapla()
    {
        $this->alis_toplam = $this->alis_fiyat * $this->adet;
        $this->satis_fiyat = $this->alis_fiyat * (1 + $this->kar_orani / 100);
        $this->satis_toplam = $this->satis_fiyat * $this->adet;
        $this->save();
    }
}
