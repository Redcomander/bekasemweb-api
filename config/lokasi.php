<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kantor Urusan Agama (KUA) Location Configuration
    |--------------------------------------------------------------------------
    |
    | Static location information for KUA Sembawa. This replaces external
    | Google Maps API with internal configuration.
    |
    */

    'nama' => 'KUA Kecamatan Sembawa',
    'alamat' => 'Jl. Raya Sembawa, Kec. Sembawa, Kab. Banyuasin, Sumatera Selatan',
    'kelurahan' => 'Sembawa',
    'kecamatan' => 'Sembawa',
    'kabupaten' => 'Banyuasin',
    'provinsi' => 'Sumatera Selatan',
    'kode_pos' => '30772',

    // GPS Coordinates (update with actual coordinates)
    'latitude' => -2.9631,
    'longitude' => 104.7527,

    // Google Maps embed URL (update with actual location)
    'google_maps_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.5!2d104.7527!3d-2.9631!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sKUA%20Sembawa!5e0!3m2!1sid!2sid!4v1234567890',

    // Contact information
    'telepon' => '0711-XXXXXXX',
    'email' => 'kua.sembawa@kemenag.go.id',
    'whatsapp' => '62811XXXXXXX',

    // Operating hours
    'jam_operasional' => [
        'senin' => '08:00 - 16:00 WIB',
        'selasa' => '08:00 - 16:00 WIB',
        'rabu' => '08:00 - 16:00 WIB',
        'kamis' => '08:00 - 16:00 WIB',
        'jumat' => '08:00 - 16:30 WIB',
        'sabtu' => 'Tutup',
        'minggu' => 'Tutup',
    ],

    // Additional info
    'website' => 'https://kua-sembawa.kemenag.go.id',
];
