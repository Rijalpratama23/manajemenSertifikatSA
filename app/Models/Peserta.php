<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peserta extends Model
{
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diubah_pada';

    protected $table = 'peserta';
    protected $primaryKey = 'id_peserta';

    protected $fillable = ['nama', 'email', 'asal_instansi'];

    public function sertifikat(): HasMany
    {
        return $this->hasMany(Sertifikat::class, 'id_peserta', 'id_peserta');
    }
}
