<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    // GET /api/absensi — Semua absensi (admin)
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $absensi = DB::table('absensi')
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->select('absensi.*', 'users.nama', 'users.jabatan', 'users.nik')
            ->whereMonth('absensi.tanggal', $bulan)
            ->whereYear('absensi.tanggal', $tahun)
            ->orderBy('absensi.tanggal', 'desc')
            ->get();

        return response()->json(['data' => $absensi]);
    }

    public function show(int $id)
    {
        $data = DB::table('absensi')
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->select('absensi.*', 'users.nama', 'users.jabatan', 'users.nik')
            ->where('absensi.id', $id)
            ->first();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'tanggal' => 'required|date',
            'status'  => 'required|in:Hadir,Izin,Sakit,SPPD',
        ]);

        $exists = DB::table('absensi')
            ->where('user_id', $request->user_id)
            ->where('tanggal', $request->tanggal)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Pegawai sudah absen pada tanggal ini.'], 409);
        }

        DB::table('absensi')->insert([
            'user_id'    => $request->user_id,
            'tanggal'    => $request->tanggal,
            'jam_masuk'  => $request->jam_masuk ?: null,
            'jam_keluar' => $request->jam_keluar ?: null,
            'status'     => $request->status,
            'keterangan' => $request->keterangan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Absensi berhasil ditambahkan.'], 201);
    }

    // POST /api/absensi/checkin — TESTING dengan hardcode userId
    public function checkin(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $userId = $authUser ? $authUser->user_id : 1;
        $today = now()->toDateString();

        $exists = DB::table('absensi')
            ->where('user_id', $userId)
            ->where('tanggal', $today)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Anda sudah melakukan absensi hari ini.'], 409);
        }

        $request->validate([
            'status'      => 'required|in:Hadir,Izin,Sakit,SPPD',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'keterangan'  => 'nullable|string',
            'foto_base64' => 'nullable|string',
            'surat_sakit' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240'
        ]);

        if ($request->status === 'Sakit' && !$request->hasFile('surat_sakit')) {
            return response()->json(['error' => 'Surat sakit wajib dilampirkan untuk status Sakit'], 422);
        }

        $dataInsert = [
            'user_id'    => $userId,
            'tanggal'    => $today,
            'jam_masuk'  => now()->toTimeString(),
            'status'     => $request->status,
            'latitude'   => $request->latitude ?? null,
            'longitude'  => $request->longitude ?? null,
            'keterangan' => $request->keterangan ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Upload foto base64 (Hadir)
        if ($request->status === 'Hadir' && $request->foto_base64) {
            try {
                $fotoData = base64_decode(preg_replace('#^data:image/\w+;base64,#', '', $request->foto_base64));
                $fotoName = 'foto_' . $userId . '_' . time() . '.jpg';
                @mkdir(public_path('uploads/foto'), 0755, true);
                file_put_contents(public_path('uploads/foto/' . $fotoName), $fotoData);
                $dataInsert['foto_path'] = 'uploads/foto/' . $fotoName;
            } catch (\Exception $e) {
                // Lanjut tanpa foto
            }
        }

        // Upload surat sakit (Sakit)
        if ($request->hasFile('surat_sakit')) {
            try {
                $file = $request->file('surat_sakit');
                $fileName = 'surat_sakit_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('surat-sakit', $fileName, 'public');

                $dataInsert['surat_sakit_path'] = $fileName;
                $dataInsert['surat_sakit_original_name'] = $file->getClientOriginalName();
                $dataInsert['surat_sakit_mime_type'] = $file->getClientMimeType();
                $dataInsert['surat_sakit_size'] = $file->getSize();
                $dataInsert['surat_sakit_uploaded_at'] = now();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal upload surat sakit'], 500);
            }
        }

        DB::table('absensi')->insert($dataInsert);

        return response()->json([
            'message' => 'Absensi berhasil dicatat.',
            'data' => [
                'tanggal'   => $today,
                'jam_masuk' => now()->format('H:i'),
                'status'    => $request->status,
            ]
        ], 201);
    }

    // POST /api/absensi/checkout — TESTING dengan hardcode userId
    public function checkout(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $userId = $authUser ? $authUser->user_id : 1;
        $today = now()->toDateString();

        $updated = DB::table('absensi')
            ->where('user_id', $userId)
            ->where('tanggal', $today)
            ->whereNull('jam_keluar')
            ->update([
                'jam_keluar' => now()->toTimeString(),
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json(['error' => 'Tidak ada absensi masuk hari ini atau sudah checkout.'], 400);
        }

        return response()->json(['message' => 'Absen pulang berhasil dicatat.']);
    }

    // GET /api/absensi/today — TESTING dengan hardcode userId
    public function today(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $userId = $authUser ? $authUser->user_id : 1;
        $today = now()->toDateString();

        $data = DB::table('absensi')
            ->where('user_id', $userId)
            ->where('tanggal', $today)
            ->first();

        return response()->json(['data' => $data]);
    }

    // GET /api/absensi/riwayat — TESTING dengan hardcode userId
    public function riwayat(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $userId = $authUser ? $authUser->user_id : 1;
        $limit = $request->get('limit', 30);

        $data = DB::table('absensi')
            ->where('user_id', $userId)
            ->orderBy('tanggal', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['data' => $data]);
    }

    // GET /api/absensi/laporan — TESTING dengan hardcode userId
    public function laporan(Request $request)
    {
        $authUser = $request->attributes->get('auth_user');
        $userId = $authUser ? $authUser->user_id : 1;
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $summary = DB::table('absensi')
            ->where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'Hadir') as hadir,
                SUM(status = 'SPPD')  as sppd,
                SUM(status = 'Izin')  as izin,
                SUM(status = 'Sakit') as sakit
            ")
            ->first();

        $detail = DB::table('absensi')
            ->where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'summary' => $summary,
            'detail'  => $detail,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'status'     => 'required|in:Hadir,Izin,Sakit,SPPD',
            'jam_masuk'  => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
        ]);

        DB::table('absensi')->where('id', $id)->update([
            'user_id'    => $request->user_id,
            'tanggal'    => $request->tanggal,
            'status'     => $request->status,
            'jam_masuk'  => $request->jam_masuk ?: null,
            'jam_keluar' => $request->jam_keluar ?: null,
            'keterangan' => $request->keterangan,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Absensi berhasil diupdate.']);
    }

    public function destroy(int $id)
    {
        $record = DB::table('absensi')->find($id);
        if ($record && $record->surat_sakit_path) {
            try {
                Storage::disk('public')->delete('surat-sakit/' . $record->surat_sakit_path);
            } catch (\Exception $e) {
                // Lanjut walau gagal
            }
        }

        DB::table('absensi')->where('id', $id)->delete();
        return response()->json(['message' => 'Absensi berhasil dihapus.']);
    }
}