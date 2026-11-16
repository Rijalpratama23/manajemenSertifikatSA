<?php

return [
    'types' => [
        'MAG' => [
            'title' => 'Data magang',
            'names' => ['magang'],
            'fields' => [
                ['key' => 'instansi', 'label' => 'Instansi/perusahaan magang', 'placeholder' => 'Contoh: PT Sukarobot', 'required' => true],
                ['key' => 'posisi', 'label' => 'Posisi atau divisi', 'placeholder' => 'Contoh: Software Developer', 'required' => true],
                ['key' => 'durasi', 'label' => 'Durasi magang', 'placeholder' => 'Contoh: 3 bulan', 'required' => true, 'column' => 'durasi'],
            ],
        ],
        'PKL' => [
            'title' => 'Data praktik kerja lapangan',
            'names' => ['praktik kerja lapangan (pkl)'],
            'fields' => [
                ['key' => 'instansi', 'label' => 'Instansi tempat PKL', 'placeholder' => 'Contoh: PT Sukarobot', 'required' => true],
                ['key' => 'bidang', 'label' => 'Bidang kegiatan', 'placeholder' => 'Contoh: Pemrograman robotika', 'required' => true],
                ['key' => 'durasi', 'label' => 'Durasi PKL', 'placeholder' => 'Contoh: 3 bulan', 'required' => true, 'column' => 'durasi'],
            ],
        ],
        'LPK' => [
            'title' => 'Data pelatihan LPK',
            'names' => ['pelatihan lpk'],
            'fields' => [
                ['key' => 'program', 'label' => 'Nama program pelatihan', 'placeholder' => 'Contoh: Pemrograman Dasar', 'required' => true],
                ['key' => 'lembaga', 'label' => 'Lembaga pelatihan', 'placeholder' => 'Nama LPK penyelenggara', 'required' => true],
                ['key' => 'durasi', 'label' => 'Durasi pelatihan', 'placeholder' => 'Contoh: 40 jam', 'required' => true, 'column' => 'durasi'],
                ['key' => 'predikat', 'label' => 'Predikat', 'placeholder' => 'Contoh: Sangat Baik', 'required' => false, 'column' => 'predikat'],
            ],
        ],
        '34' => [
            'title' => 'Data kursus',
            'names' => ['kursus'],
            'fields' => [
                ['key' => 'nama_kursus', 'label' => 'Nama kursus', 'placeholder' => 'Contoh: Desain 3D', 'required' => true],
                ['key' => 'penyelenggara', 'label' => 'Penyelenggara kursus', 'placeholder' => 'Nama lembaga atau instruktur', 'required' => true],
                ['key' => 'durasi', 'label' => 'Durasi kursus', 'placeholder' => 'Contoh: 20 jam', 'required' => true, 'column' => 'durasi'],
                ['key' => 'nilai', 'label' => 'Nilai', 'placeholder' => 'Contoh: 92', 'required' => false, 'column' => 'nilai'],
            ],
        ],
        'SRC' => [
            'title' => 'Data kompetisi robotik',
            'names' => ['sukabumi robotic competitions'],
            'fields' => [
                ['key' => 'nama_kompetisi', 'label' => 'Nama kompetisi', 'placeholder' => 'Contoh: Sukabumi Robotic Competition', 'required' => true],
                ['key' => 'kategori', 'label' => 'Kategori/cabang lomba', 'placeholder' => 'Contoh: Robot Soccer', 'required' => true],
                ['key' => 'prestasi', 'label' => 'Prestasi/peringkat', 'placeholder' => 'Contoh: Juara 1', 'required' => true],
                ['key' => 'nama_tim', 'label' => 'Nama tim', 'placeholder' => 'Nama tim peserta (opsional)', 'required' => false],
            ],
        ],
        'UJIKOM' => [
            'title' => 'Data uji kompetensi',
            'names' => ['uji kompetensi dudi', 'uji kompetensi'],
            'fields' => [
                ['key' => 'skema', 'label' => 'Skema kompetensi', 'placeholder' => 'Contoh: Teknisi Robotika', 'required' => true],
                ['key' => 'bidang', 'label' => 'Bidang kompetensi', 'placeholder' => 'Contoh: Otomasi dan robotika', 'required' => true],
                ['key' => 'nilai', 'label' => 'Nilai uji kompetensi', 'placeholder' => 'Contoh: 92', 'required' => true, 'column' => 'nilai'],
                ['key' => 'predikat', 'label' => 'Predikat', 'placeholder' => 'Contoh: Kompeten', 'required' => false, 'column' => 'predikat'],
            ],
        ],
    ],
];
