<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiyatTeklif extends Model
{
    protected $table = 'fiyat_teklifleri';
    
    protected $fillable = [
        'teklif_no',
        'musteri_id',
        'yetkili_adi',
        'yetkili_email',
        'tarih',
        'gecerlilik_tarihi',
        'durum',
        'giris_metni',
        'ek_notlar',
        'teklif_kosullari',
        'logo_path',
        'imza_path',
        'kar_orani_varsayilan',
        'toplam_alis',
        'toplam_satis',
        'toplam_kar',
    ];

    protected $casts = [
        'tarih' => 'date',
        'gecerlilik_tarihi' => 'date',
    ];

    public function musteri()
    {
        return $this->belongsTo(Musteri::class);
    }

    public function kalemler()
    {
        return $this->hasMany(TeklifKalem::class, 'teklif_id');
    }

    public function hesaplaToplamlar()
    {
        $this->toplam_alis = $this->kalemler()->sum('alis_toplam');
        $this->toplam_satis = $this->kalemler()->sum('satis_toplam');
        $this->toplam_kar = $this->toplam_satis - $this->toplam_alis;
        $this->save();
    }
}
