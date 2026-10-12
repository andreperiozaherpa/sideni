<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Service Level Agreement (SLA) per tahap penanganan laporan
    |--------------------------------------------------------------------------
    |
    | Batas waktu (menit) untuk setiap status laporan, mengacu pada SOP
    | pelaporan SI-DENI. Status terminal (completed/rejected) tidak memiliki
    | batas waktu lanjutan.
    |
    */
    'sla' => [
        'pending' => ['minutes' => 40, 'label' => 'Pengecekan & validasi awal'],
        'needs_clarification' => ['minutes' => 1440, 'label' => 'Klarifikasi oleh kampung'],
        'verified' => ['minutes' => 15, 'label' => 'Penerbitan disposisi'],
        'in_progress' => ['minutes' => 2880, 'label' => 'Tindak lanjut lapangan'],
        'completed' => ['minutes' => 1440, 'label' => 'Penyusunan laporan akhir'],
        'rejected' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifikasi
    |--------------------------------------------------------------------------
    |
    | Aktifkan kanal email di samping notifikasi in-app (database) bila
    | konfigurasi mail sudah siap.
    |
    */
    'notifications' => [
        'mail' => (bool) env('SIDENI_NOTIFY_MAIL', false),
    ],
];
