<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function bulanan(Request $request) {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $data = DB::table('users')
            ->where('aktif', 1)
            ->where('role', '!=', 'admin')
            ->get()
            ->map(function ($user) use ($request) {
                $absensi = DB::table('absensi')
                    ->where('user_id', $user->id)
                    ->whereMonth('tanggal', $request->bulan)
                    ->whereYear('tanggal', $request->tahun)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(status = 'hadir') as hadir,
                        SUM(status = 'izin') as izin,
                        SUM(status = 'alpha') as alpha,
                        SUM(status = 'cuti') as cuti
                    ")
                    ->first();

                $persentase = $absensi->total > 0
                    ? round(($absensi->hadir / $absensi->total) * 100, 2)
                    : 0;

                return [
                    'user_id'    => $user->id,
                    'nik'        => $user->nik,
                    'nama'       => $user->nama,
                    'jabatan'    => $user->jabatan,
                    'hadir'      => $absensi->hadir ?? 0,
                    'izin'       => $absensi->izin ?? 0,
                    'alpha'      => $absensi->alpha ?? 0,
                    'cuti'       => $absensi->cuti ?? 0,
                    'persentase' => $persentase,
                ];
            });

        return response()->json([
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'data'  => $data,
        ]);
    }

    public function rekapPegawai(Request $request) {
        $data = DB::table('users')
            ->where('aktif', 1)
            ->where('role', '!=', 'admin')
            ->select('id', 'nik', 'nama', 'jabatan', 'role')
            ->get()
            ->map(function ($user) {
                $total = DB::table('absensi')
                    ->where('user_id', $user->id)
                    ->selectRaw("
                        SUM(status = 'hadir') as hadir,
                        SUM(status = 'izin') as izin,
                        SUM(status = 'alpha') as alpha,
                        SUM(status = 'cuti') as cuti
                    ")
                    ->first();

                return [
                    'nik'     => $user->nik,
                    'nama'    => $user->nama,
                    'jabatan' => $user->jabatan,
                    'hadir'   => $total->hadir ?? 0,
                    'izin'    => $total->izin ?? 0,
                    'alpha'   => $total->alpha ?? 0,
                    'cuti'    => $total->cuti ?? 0,
                ];
            });

        return response()->json($data);
    }
}