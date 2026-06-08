<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request) {
        $today = now()->toDateString();

        $totalHadir = DB::table('absensi')
            ->where('tanggal', $today)
            ->where('status', 'hadir')
            ->count();

        $totalIzin = DB::table('absensi')
            ->where('tanggal', $today)
            ->where('status', 'izin')
            ->count();

        $totalAlpha = DB::table('absensi')
            ->where('tanggal', $today)
            ->where('status', 'alpha')
            ->count();

        // Hanya hitung role 'user' (pegawai biasa)
        $totalPegawai = DB::table('users')
            ->where('aktif', 1)
            ->where('role', 'user')
            ->count();

        $sppdMenunggu = DB::table('sppd')
            ->where('status', 'menunggu')
            ->count();

        $totalHariKerja = DB::table('absensi')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->distinct('tanggal')
            ->count('tanggal');

        $totalHadirBulan = DB::table('absensi')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->where('status', 'hadir')
            ->count();

        $persentase = ($totalHariKerja > 0 && $totalPegawai > 0)
            ? round(($totalHadirBulan / ($totalHariKerja * $totalPegawai)) * 100)
            : 0;

        $sppdList = DB::table('sppd')
            ->join('users', 'users.id', '=', 'sppd.user_id')
            ->where('sppd.status', 'menunggu')
            ->select('sppd.*', 'users.nama', 'users.jabatan')
            ->orderBy('sppd.created_at', 'desc')
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