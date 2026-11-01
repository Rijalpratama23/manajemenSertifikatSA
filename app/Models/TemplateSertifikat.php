<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateSertifikat extends Model
{
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $table = 'template_sertifikat';
    protected $primaryKey = 'id_template';

    protected $fillable = ['id_jenis', 'nama_template', 'berkas_desain', 'konfigurasi_posisi', 'aktif'];

    protected function casts(): array
    {
        return [
            'konfigurasi_posisi' => 'array',
            'aktif' => 'boolean',
        ];
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisSertifikat::class, 'id_jenis', 'id_jenis');
    }

    public function sertifikat(): HasMany
    {
        return $this->hasMany(Sertifikat::class, 'id_template', 'id_template');
    }
}
