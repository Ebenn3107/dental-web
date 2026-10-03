<?php

return [
    'name' => env('CLINIC_NAME', 'Klinik Gigi Sehat'),

    // Nomor WhatsApp admin untuk konfirmasi booking (format internasional tanpa +)
    'whatsapp_number' => env('CLINIC_WHATSAPP_NUMBER', '6281234567890'),

    // Foto statis halaman publik (public/images). Foto layanan dipetakan
    // berdasarkan nama layanan; layanan tanpa pasangan tampil tanpa foto.
    'images' => [
        'hero' => 'images/hero.jpg',
        'cta' => 'images/cta.jpg',
        'services' => [
            'Pemeriksaan & Konsultasi' => 'images/layanan-1.jpg',
            'Scaling (Pembersihan Karang Gigi)' => 'images/layanan-2.jpg',
            'Tambal Gigi' => 'images/layanan-3.jpg',
            'Cabut Gigi' => 'images/layanan-4.jpg',
            'Bleaching Gigi' => 'images/layanan-5.jpg',
        ],
    ],
];
