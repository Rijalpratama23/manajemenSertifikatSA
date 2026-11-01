<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Akun admin (F-01). Satu-satunya pengguna yang login.
 * Kolom kata sandi pada ERD bernama password_hash, jadi getAuthPassword() diarahkan ke sana.
 */
class Admin extends Authenticatable
{
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = ['nama', 'username', 'password_hash'];
    protected $hidden = ['password_hash'];

    protected function casts(): array
    {
        return ['password_hash' => 'hashed'];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // Tabel admin tidak punya kolom remember_token.
    public function getRememberTokenName(): string
    {
        return '';
    }

    public function sertifikat(): HasMany
    {
        return $this->hasMany(Sertifikat::class, 'id_admin', 'id_admin');
    }
}
