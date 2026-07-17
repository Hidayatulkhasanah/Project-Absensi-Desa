<?php

namespace App\Models;

use App\Enums\SppdStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sppd extends Model
{
    use HasFactory;

    protected $table = 'sppd';

    protected $fillable = [
        'user_id',
        'nomor_sppd',
        'tujuan',
        'keperluan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'status',
        'keterangan',
        'file_sppd',
    ];

    protected $casts = [
        // Explicit Y-m-d format: JSON responses must stay plain date strings,
        // matching the raw DB column (frontend interpolates these directly).
        'tanggal_berangkat' => 'date:Y-m-d',
        'tanggal_kembali' => 'date:Y-m-d',
        'status' => SppdStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}