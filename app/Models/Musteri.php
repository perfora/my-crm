<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Musteri extends Model
{
    protected $table = 'musteriler';
    
    protected $fillable = [
        'sirket',
        'sehir',
        'adres',
        'telefon',
        'notlar',
        'derece',
        'turu',
        'notion_id',
        'notion_url',
    ];

    // İlişkiler
    public function tumIsler()
    {
        return $this->hasMany(TumIsler::class);
    }

    public function kisiler()
    {
        return $this->hasMany(Kisi::class);
    }

    public function ziyaretler()
    {
        return $this->hasMany(Ziyaret::class);
    }

    // En Son Ziyaret (rollup)
    public function getEnSonZiyaretAttribute()
    {
        $sonZiyaret = $this->ziyaretler()
            ->whereNotNull('ziyaret_tarihi')
            ->orderBy('ziyaret_tarihi', 'desc')
            ->first();
        
        return $sonZiyaret ? $sonZiyaret->ziyaret_tarihi : null;
    }

    // Ziyaret Adeti (rollup)
    public function getZiyaretAdetiAttribute()
    {
        return $this->ziyaretler()->count();
    }

    // Ziyaret Gün (formula - son ziyaretten bugüne kaç gün)
    public function getZiyaretGunAttribute()
    {
        if ($this->en_son_ziyaret) {
            // Bugünden son ziyaret tarihini çıkar (pozitif = geçmiş, negatif = gelecek)
            return (int) \Carbon\Carbon::parse($this->en_son_ziyaret)->diffInDays(now(), false);
        }
        return null;
    }

    // Toplam Teklif (rollup)
    public function getToplamTeklifAttribute()
    {
        return $this->tumIsler()->sum('teklif_tutari') ?? 0;
    }

    // Kazanıldı Toplamı (rollup - sadece Kazanıldı statüsündeki işler)
    public function getKazanildiToplamiAttribute()
    {
        return $this->tumIsler()
            ->where('tipi', 'Kazanıldı')
            ->sum('teklif_tutari') ?? 0;
    }

    // Tedarikçi olarak fiyat kayıtları
    public function fiyatlar()
    {
        return $this->hasMany(TedarikiciFiyat::class, 'musteri_id');
    }

    // Tedarikçi olarak teklif kalemleri
    public function teklifKalemleri()
    {
        return $this->hasMany(TeklifKalem::class, 'musteri_id');
    }
}