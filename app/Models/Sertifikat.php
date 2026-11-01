<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class Sertifikat extends Model
{
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    // Tabel sertifikat tidak memakai created_at/updated_at; tanggal_terbit diisi eksplisit.
    public $timestamps = false;

    protected $table = 'sertifikat';
    protected $primaryKey = 'id_sertifikat';

    protected $fillable = [
        'nomor_sertifikat', 'token_publik', 'kode_verifikasi',
        'id_peserta', 'id_jenis', 'id_admin', 'id_template',
        'periode_mulai', 'periode_selesai', 'durasi', 'nilai', 'predikat',
        'tanggal_terbit', 'status', 'berkas_pdf',
        'alasan_batal', 'dibatalkan_pada',
    ];

    // Email dan data internal tidak boleh ikut ke respons publik.
    protected $hidden = ['id_admin', 'berkas_pdf'];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
            'tanggal_terbit' => 'date',
            'dibatalkan_pada' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Integritas data: nomor, token, dan kode tidak boleh diubah setelah terbit.
        // Jika ada kesalahan, batalkan lalu terbitkan ulang dengan nomor baru (F-12).
        static::updating(function (Sertifikat $sertifikat) {
            foreach (['nomor_sertifikat', 'token_publik', 'kode_verifikasi'] as $kolom) {
                if ($sertifikat->isDirty($kolom)) {
                    throw new LogicException("Kolom {$kolom} tidak boleh diubah setelah sertifikat terbit.");
                }
            }
        });
    }

    public function isAktif(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id_peserta');
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisSertifikat::class, 'id_jenis', 'id_jenis');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateSertifikat::class, 'id_template', 'id_template');
    }
}
