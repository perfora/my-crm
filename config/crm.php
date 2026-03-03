<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Varsayılan Raporlama Kuru
    |--------------------------------------------------------------------------
    |
    | API raporlarında TL tutarları USD'ye çevirirken kullanılacak
    | varsayılan kur değeri. Bu, TCMB'den kur alınamadığı
    | durumlarda bir yedek olarak kullanılır.
    |
    */
    'default_usd_rate' => 35.0,

    'timezone' => env('APP_TIMEZONE', 'Europe/Istanbul'),
    'date_format' => 'd.m.Y',
    'datetime_format' => 'd.m.Y H:i',
    'datetime_seconds_format' => 'd.m.Y H:i:s',
];
