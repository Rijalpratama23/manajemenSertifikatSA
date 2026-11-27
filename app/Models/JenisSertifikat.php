<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSertifikat extends Model
{
    public $timestamps = false;

    protected $table = 'jenis_sertifikat';
    protected $primaryKey = 'id_jenis';

    protected $fillable = ['kode', 'nama_jenis', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function template(): HasMany
    {
        return $this->hasMany(TemplateSertifikat::class, 'id_jenis', 'id_jenis');
    }

    public function sertifikat(): HasMany
    {
        return $this->hasMany(Sertifikat::class, 'id_jenis', 'id_jenis');
    }

    public function peserta(): BelongsToMany
    {
        return $this->belongsToMany(
            Peserta::class,
            'peserta_jenis_sertifikat',
            'id_jenis',
            'id_peserta',
            'id_jenis',
            'id_peserta',
        );
    }

    public function nomorUrut(): HasMany
    {
        return $this->hasMany(NomorUrut::class, 'id_jenis', 'id_jenis');
    }
}
