<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';
    
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status',
        'keterangan',
        'foto_masuk',
        'foto_keluar',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_keluar',
        'longitude_keluar',
        // Kolom untuk surat sakit
        'surat_sakit_path',
        'surat_sakit_original_name',
        'surat_sakit_mime_type',
        'surat_sakit_size',
        'surat_sakit_uploaded_at'
    ];

    protected $dates = [
        'tanggal',
        'surat_sakit_uploaded_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'surat_sakit_uploaded_at' => 'datetime',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check apakah attendance ini punya surat sakit
     */
    public function hasSuratSakit()
    {
        return !is_null($this->surat_sakit_path);
    }

    /**
     * Get full URL untuk download surat sakit
     */
    public function getSuratSakitUrl()
    {
        if ($this->hasSuratSakit()) {
            return route('attendance.download-surat-sakit', $this->id);
        }
        return null;
    }

    /**
     * Get extension file surat sakit
     */
    public function getSuratSakitExtension()
    {
        if ($this->hasSuratSakit()) {
            return pathinfo($this->surat_sakit_original_name, PATHINFO_EXTENSION);
        }
        return null;
    }

    /**
     * Get formatted file size
     */
    public function getSuratSakitFormattedSize()
    {
        if ($this->hasSuratSakit()) {
            $bytes = $this->surat_sakit_size;
            $units = ['B', 'KB', 'MB', 'GB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= (1 << (10 * $pow));
            return round($bytes, 2) . ' ' . $units[$pow];
        }
        return null;
    }

    /**
     * Scope: Get attendance di dalam radius tertentu
     */
    public function scopeInRadius($query, $centerLat, $centerLng, $radiusMeters = 100)
    {
        return $query->whereRaw(
            "ST_Distance_Sphere(
                POINT(?, ?),
                POINT(latitude_masuk, longitude_masuk)
            ) <= ?",
            [$centerLng, $centerLat, $radiusMeters]
        );
    }

    /**
     * Scope: Get attendance dengan status tertentu
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get attendance hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', today());
    }

    /**
     * Scope: Get attendance bulan ini
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year);
    }

    /**
     * Scope: Get attendance yang punya surat sakit
     */
    public function scopeWithSuratSakit($query)
    {
        return $query->whereNotNull('surat_sakit_path');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        $classes = [
            'Hadir' => 'bdg-h',
            'Izin' => 'bdg-i',
            'Sakit' => 'bdg-sk',
            'SPPD' => 'bdg-s',
            'Alpha' => 'bdg-a',
        ];
        
        return $classes[$this->status] ?? 'bdg-default';
    }

    /**
     * Get status icon emoji
     */
    public function getStatusIcon()
    {
        $icons = [
            'Hadir' => '✅',
            'Izin' => '📝',
            'Sakit' => '🤒',
            'SPPD' => '✈️',
            'Alpha' => '❌',
        ];
        
        return $icons[$this->status] ?? '📋';
    }

    /**
     * Check apakah user terlambat
     */
    public function isLate()
    {
        if ($this->jam_masuk && $this->status === 'Hadir') {
            return $this->jam_masuk > '08:00:00';
        }
        return false;
    }

    /**
     * Get duration bekerja (jam_keluar - jam_masuk)
     */
    public function getWorkingDuration()
    {
        if ($this->jam_masuk && $this->jam_keluar) {
            $masuk = \Carbon\Carbon::createFromFormat('H:i:s', $this->jam_masuk);
            $keluar = \Carbon\Carbon::createFromFormat('H:i:s', $this->jam_keluar);
            return $keluar->diffInHours($masuk);
        }
        return null;
    }

    /**
     * Validasi status yang memerlukan surat sakit
     */
    public static function requiresSuratSakit()
    {
        return ['Sakit'];
    }

    /**
     * Validasi status yang memerlukan foto
     */
    public static function requiresFoto()
    {
        return ['Hadir'];
    }

    /**
     * Validasi status yang memerlukan GPS
     */
    public static function requiresGPS()
    {
        return ['Hadir'];
    }
}