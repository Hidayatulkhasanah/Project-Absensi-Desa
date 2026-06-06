<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    // GET /api/absensi — Semua absensi (admin/operator)
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $absensi = DB::table('absensi')
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->select('absensi.*', 'users.nama', 'users.jabatan')
            ->whereMonth('absensi.tanggal', $bulan)
            ->whereYear('absensi.tanggal', $tahun)
            ->orderBy('absensi.tanggal', 'desc')
            ->get();

        return response()->json(['data' => $absensi]);
    }

    // POST /api/absensi/checkin — Absen masuk
    public function checkin(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $userId   = $authUser->user_id;
        $today    = now()->toDateString();

        // Cek sudah absen hari ini
        $exists = DB::table('absensi')
            ->where('user_id', $userId)
            ->where('tanggal', $today)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Anda sudah melakukan absensi hari ini.'
            ], 409);
        }

        $request->validate([
            'status'    => 'required|in:Hadir,Izin,SPPD',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Upload foto base64 jika ada
        $fotoPath = null;
        if ($request->status === 'Hadir' && $request->foto_base64) {
            $fotoData = base64_decode(
                preg_replace('#^data:image/\w+;base64,#', '', $request->foto_base64)
            );
            $fotoName = 'foto_' . $userId . '_' . time() . '.jpg';
            $fotoPath = 'uploads/foto/' . $fotoName;
            file_put_contents(public_path($fotoPath), $fotoData);
        }

        DB::table('absensi')->insert([
            'user_id'    => $userId,
            'tanggal'    => $today,
            'jam_masuk'  => now()->toTimeString(),
            'status'     => $request->status,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'foto_path'  => $fotoPath,
            'keterangan' => $request->keterangan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Absensi berhasil dicatat.',
            'data'    => [
                'tanggal'   => $today,
                'jam_masuk' => now()->format('H:i'),
                'status'    => $request->status,
            ]
        ], 201);
    }

    // POST /api/absensi/checkout — Absen pulang
    public function checkout(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $today    = now()->toDateString();

        $updated = DB::table('absensi')
            ->where('user_id', $authUser->user_id)
            ->where('tanggal', $today)
            ->whereNull('jam_keluar')
            ->update([
                'jam_keluar' => now()->toTimeString(),
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json([
                'error' => 'Tidak ada absensi masuk hari ini atau sudah checkout.'
            ], 400);
        }

        return response()->json([
            'message'    => 'Absen pulang berhasil dicatat.',
            'jam_keluar' => now()->format('H:i'),
        ]);
    }

    // GET /api/absensi/today — Cek absensi hari ini
    public function today(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $today    = now()->toDateString();

        $data = DB::table('absensi')
            ->where('user_id', $authUser->user_id)
            ->where('tanggal', $today)
            ->first();

        return response()->json(['data' => $data]);
    }

    // GET /api/absensi/riwayat — Riwayat absensi milik user
    public function riwayat(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $limit    = min((int)($request->limit ?? 30), 100);
        $offset   = (int)($request->offset ?? 0);

        $query = DB::table('absensi')
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->where('absensi.user_id', $authUser->user_id)
            ->select('absensi.*', 'users.nama')
            ->orderBy('absensi.tanggal', 'desc');

        if ($request->status && in_array($request->status, ['Hadir', 'Izin', 'Alpha', 'SPPD'])) {
            $query->where('absensi.status', $request->status);
        }

        $data = $query->offset($offset)->limit($limit)->get();

        return response()->json(['data' => $data]);
    }

    // GET /api/absensi/laporan — Laporan bulanan user
    public function laporan(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $bulan    = $request->bulan ?? now()->month;
        $tahun    = $request->tahun ?? now()->year;

        $summary = DB::table('absensi')
            ->where('user_id', $authUser->user_id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'Hadir') as hadir,
                SUM(status = 'SPPD')  as sppd,
                SUM(status = 'Izin')  as izin,
                SUM(status = 'Alpha') as alpha
            ")
            ->first();

        $detail = DB::table('absensi')
            ->where('user_id', $authUser->user_id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'summary' => $summary,
            'detail'  => $detail,
        ]);
    }

    // PUT /api/absensi/{id} — Update absensi (operator/admin)
    public function update(Request $request, int $id)
    {
        $request->validate([
            'status'     => 'required|in:Hadir,Izin,Alpha,SPPD,cuti',
            'jam_masuk'  => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
        ]);

        DB::table('absensi')->where('id', $id)->update([
            'status'     => $request->status,
            'jam_masuk'  => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'keterangan' => $request->keterangan,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Absensi berhasil diupdate.']);
    }

    // DELETE /api/absensi/{id} — Hapus absensi (operator/admin)
    public function destroy(int $id)
    {
        DB::table('absensi')->where('id', $id)->delete();
        return response()->json(['message' => 'Absensi berhasil dihapus.']);
    }
}