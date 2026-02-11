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
        'arama_periyodu_gun',
        'ziyaret_periyodu_gun',
        'temas_kurali',
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

    // En son baglanti tarihi (ziyaret veya telefon)
    public function getSonBaglantiTarihiAttribute()
    {
        if ($this->relationLoaded('ziyaretler')) {
            $latest = $this->ziyaretler
                ->map(function ($item) {
                    $visitAt = $item->ziyaret_tarihi;
                    $callAt = $item->arama_tarihi;

                    if (!$visitAt && !$callAt) {
                        return null;
                    }

                    if ($visitAt && !$callAt) {
                        return $visitAt;
                    }

                    if (!$visitAt && $callAt) {
                        return $callAt;
                    }

                    return $visitAt->greaterThanOrEqualTo($callAt) ? $visitAt : $callAt;
                })
                ->filter()
                ->sortDesc()
                ->first();

            return $latest ?: null;
        }

        $sonZiyaret = $this->ziyaretler()->whereNotNull('ziyaret_tarihi')->max('ziyaret_tarihi');
        $sonArama = $this->ziyaretler()->whereNotNull('arama_tarihi')->max('arama_tarihi');

        if (!$sonZiyaret && !$sonArama) {
            return null;
        }

        if ($sonZiyaret && !$sonArama) {
            return \Carbon\Carbon::parse($sonZiyaret);
        }

        if (!$sonZiyaret && $sonArama) {
            return \Carbon\Carbon::parse($sonArama);
        }

        $ziyaretTarih = \Carbon\Carbon::parse($sonZiyaret);
        $aramaTarih = \Carbon\Carbon::parse($sonArama);
        return $ziyaretTarih->greaterThanOrEqualTo($aramaTarih) ? $ziyaretTarih : $aramaTarih;
    }

    // En son baglanti tipi (Ziyaret / Telefon)
    public function getSonBaglantiTuruAttribute()
    {
        if ($this->relationLoaded('ziyaretler')) {
            $record = $this->ziyaretler
                ->map(function ($item) {
                    $latestAt = $item->ziyaret_tarihi;
                    $type = 'Ziyaret';

                    if ($item->arama_tarihi && (!$latestAt || $item->arama_tarihi->greaterThan($latestAt))) {
                        $latestAt = $item->arama_tarihi;
                        $type = 'Telefon';
                    }

                    if (!$latestAt) {
                        return null;
                    }

                    $normalized = $item->tur;
                    if (!$normalized) {
                        $normalized = $type;
                    } elseif (in_array(mb_strtolower($normalized), ['arama', 'telefon'], true)) {
                        $normalized = 'Telefon';
                    } elseif (mb_strtolower($normalized) === 'ziyaret') {
                        $normalized = 'Ziyaret';
                    }

                    return [
                        'date' => $latestAt,
                        'type' => $normalized,
                    ];
                })
                ->filter()
                ->sortByDesc('date')
                ->first();

            return $record['type'] ?? null;
        }

        $tarih = $this->son_baglanti_tarihi;
        if (!$tarih) {
            return null;
        }

        $record = $this->ziyaretler()
            ->where(function ($q) use ($tarih) {
                $q->where('ziyaret_tarihi', $tarih)
                    ->orWhere('arama_tarihi', $tarih);
            })
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            return null;
        }

        return $record->tur ?: ($record->arama_tarihi ? 'Telefon' : 'Ziyaret');
    }

    // Geriye uyumluluk: "En Son Ziyaret" accessor'u artik son baglanti tarihini doner
    public function getEnSonZiyaretAttribute()
    {
        return $this->son_baglanti_tarihi;
    }

    // Ziyaret Adeti (rollup)
    public function getZiyaretAdetiAttribute()
    {
        return $this->ziyaretler()->count();
    }

    // Baglanti gunu (son baglantidan bugune kac gun)
    public function getZiyaretGunAttribute()
    {
        if ($this->son_baglanti_tarihi) {
            return (int) \Carbon\Carbon::parse($this->son_baglanti_tarihi)->diffInDays(now(), false);
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
