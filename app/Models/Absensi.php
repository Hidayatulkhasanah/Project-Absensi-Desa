<?php

namespace App\Models;

use App\Enums\AbsensiStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'user_id',
        'tanggal',
        'status',
        'jam_masuk',
        'jam_keluar',
        'latitude',
        'longitude',
        'foto_path',
        'keterangan',
        'surat_sakit_path',
        'surat_sakit_original_name',
        'surat_sakit_mime_type',
        'surat_sakit_size',
        'surat_sakit_uploaded_at',
    ];

    protected $casts = [
        // Explicit Y-m-d format: JSON responses must stay plain date strings
        // (matching the raw DB column), not ISO8601 datetimes, since the
        // frontend does string comparisons/interpolation on this field.
        'tanggal' => 'date:Y-m-d',
        'status' => AbsensiStatus::class,
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'surat_sakit_uploaded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
