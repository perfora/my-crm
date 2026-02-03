<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeklifKosulu extends Model
{
    protected $table = 'teklif_kosullari';

    protected $fillable = [
        'baslik',
        'icerik',
        'varsayilan',
        'sira'
    ];

    protected $casts = [
        'varsayilan' => 'boolean',
    ];

    /**
     * Varsayılan koşulu getir
     */
    public static function varsayilan()
    {
        return static::where('varsayilan', true)->orderBy('sira')->first();
    }

    /**
     * Tümünü sıralı getir
     */
    public static function tumunuSirali()
    {
        return static::orderBy('sira')->orderBy('baslik')->get();
    }
}
