<?php

namespace App\Http\Controllers;

use App\Enums\AbsensiStatus;
use App\Enums\SppdStatus;
use App\Enums\UserRole;
use App\Models\Absensi;
use App\Models\Sppd;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request) {
        $today = now()->toDateString();

        $totalHadir = Absensi::where('tanggal', $today)
            ->where('status', AbsensiStatus::Hadir->value)
            ->count();

        $totalIzin = Absensi::where('tanggal', $today)
            ->where('status', AbsensiStatus::Izin->value)
            ->count();

        $totalAlpha = Absensi::where('tanggal', $today)
            ->where('status', AbsensiStatus::Alpha->value)
            ->count();

        // Hanya hitung role 'user' (pegawai biasa)
        $totalPegawai = User::where('aktif', 1)
            ->where('role', UserRole::User->value)
            ->count();

        $sppdMenunggu = Sppd::where('status', SppdStatus::Menunggu->value)->count();

        $totalHariKerja = Absensi::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->distinct('tanggal')
            ->count('tanggal');

        $totalHadirBulan = Absensi::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->where('status', AbsensiStatus::Hadir->value)
            ->count();

        $persentase = ($totalHariKerja > 0 && $totalPegawai > 0)
            ? round(($totalHadirBulan / ($totalHariKerja * $totalPegawai)) * 100)
            : 0;

        $sppdList = Sppd::query()
            ->join('users', 'users.id', '=', 'sppd.user_id')
            ->where('sppd.status', SppdStatus::Menunggu->value)
            ->select('sppd.*', 'users.nama', 'users.jabatan')
            ->orderByDesc('sppd.created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'today'                => $today,
            'total_hadir'          => $totalHadir,
            'total_izin'           => $totalIzin,
            'total_alpha'          => $totalAlpha,
            'total_pegawai'        => $totalPegawai,
            'sppd_menunggu'        => $sppdMenunggu,
            'persentase_kehadiran' => $persentase,
            'total_hari_kerja'     => $totalHariKerja,
            'total_hadir_bulan'    => $totalHadirBulan,
            'sppd_list'            => $sppdList,
        ]);
    }
}
