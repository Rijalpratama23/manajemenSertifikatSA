<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pencatat urutan nomor sertifikat per jenis per tahun (F-06).
 * Penambahan nomor_terakhir harus dilakukan di dalam transaksi dengan lockForUpdate().
 */
class NomorUrut extends Model
{
    public $timestamps = false;

    protected $table = 'nomor_urut';
    protected $primaryKey = 'id_nomor';

    protected $fillable = ['id_jenis', 'tahun', 'nomor_terakhir'];

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisSertifikat::class, 'id_jenis', 'id_jenis');
    }
}
