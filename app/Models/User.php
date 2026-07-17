<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nik', 'nama', 'password', 'jabatan', 'role', 'aktif'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'role' => UserRole::class,
    ];

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function sppd(): HasMany
    {
        return $this->hasMany(Sppd::class);
    }
}