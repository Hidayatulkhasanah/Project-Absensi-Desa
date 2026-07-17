<?php

namespace App\Http\Controllers;

use App\Enums\AbsensiStatus;
use App\Exports\AbsensiExport;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    // GET /api/absensi/export?dari=Y-m-d&sampai=Y-m-d&label=... (admin)
    // Server-side detailed attendance-log xlsx for a date range.
    public function export(Request $request, AbsensiExport $export)
    {
        $validated = $request->validate([
            'dari'   => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
            'label'  => 'nullable|string|max:100',
        ]);

        // Raw query builder (not Eloquent) so cells receive plain strings, not
        // enum/Carbon cast objects that PhpSpreadsheet can't serialize.
        $rows = DB::table('absensi')
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->select('absensi.*', 'users.nama', 'users.jabatan', 'users.nik')
            ->whereBetween('absensi.tanggal', [$validated['dari'], $validated['sampai']])
            ->orderBy('absensi.tanggal')
            ->orderBy('users.nama')
            ->get();

        $label = $validated['label'] ?? ($validated['dari'] . ' s.d. ' . $validated['sampai']);
        $filename = 'Absensi_' . str_replace('-', '', $validated['dari']) . '_' . str_replace('-', '', $validated['sampai']) . '.xlsx';

        return $export($rows, $label, $filename);
    }

    // GET /api/absensi — Semua absensi (admin).
    // ?paginate=1 returns Laravel's paginator shape for the Kelola Absensi table;
    // other callers (export, notifications, badges) rely on the full unpaginated
    // list, so pagination stays strictly opt-in.
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $query = Absensi::query()
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->select('absensi.*', 'users.nama', 'users.jabatan', 'users.nik')
            ->whereMonth('absensi.tanggal', $bulan)
            ->whereYear('absensi.tanggal', $tahun)
            ->orderByDesc('absensi.tanggal');

        if ($request->boolean('paginate')) {
            return response()->json($query->paginate($request->integer('per_page', 15))->withQueryString());
        }

        return response()->json(['data' => $query->get()]);
    }

    // GET /api/absensi/{id} — Detail absensi by id (admin)
    public function show(int $id)
    {
        $data = Absensi::query()
            ->join('users', 'users.id', '=', 'absensi.user_id')
            ->select('absensi.*', 'users.nama', 'users.jabatan', 'users.nik')
            ->where('absensi.id', $id)
            ->first();

        return response()->json(['data' => $data]);
    }

    // POST /api/absensi — Tambah absensi manual (admin)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'tanggal' => 'required|date',
            'status'  => ['required', Rule::in(AbsensiStatus::adminFormValues())],
        ]);

        $exists = Absensi::where('user_id', $request->user_id)
            ->where('tanggal', $request->tanggal)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Pegawai sudah absen pada tanggal ini.'
            ], 409);
        }

        Absensi::create([
            'user_id'    => $request->user_id,
            'tanggal'    => $request->tanggal,
            'jam_masuk'  => $request->jam_masuk ?: null,
            'jam_keluar' => $request->jam_keluar ?: null,
            'status'     => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(['message' => 'Absensi berhasil ditambahkan.'], 201);
    }

    // POST /api/absensi/checkin — Absen masuk (user)
    public function checkin(Request $request)
    {
        $authUser = $request->user();
        $userId   = $authUser->id;
        $today    = now()->toDateString();

        $exists = Absensi::where('user_id', $userId)
            ->where('tanggal', $today)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Anda sudah melakukan absensi hari ini.'
            ], 409);
        }

        $request->validate([
            'status'    => ['required', Rule::in(AbsensiStatus::checkinValues())],
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Upload foto base64 jika ada
        $fotoPath = null;
        if ($request->status === AbsensiStatus::Hadir->value && $request->foto_base64) {
            $fotoData = base64_decode(
                preg_replace('#^data:image/\w+;base64,#', '', $request->foto_base64)
            );
            $fotoName = 'foto_' . $userId . '_' . time() . '.jpg';
            $fotoPath = 'uploads/foto/' . $fotoName;
            @mkdir(public_path('uploads/foto'), 0755, true);
            file_put_contents(public_path($fotoPath), $fotoData);
        }

        Absensi::create([
            'user_id'    => $userId,
            'tanggal'    => $today,
            'jam_masuk'  => now()->toTimeString(),
            'status'     => $request->status,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'foto_path'  => $fotoPath,
            'keterangan' => $request->keterangan,
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

    // POST /api/absensi/checkout — Absen pulang (user)
    public function checkout(Request $request)
    {
        $authUser = $request->user();
        $today    = now()->toDateString();

        $updated = Absensi::where('user_id', $authUser->id)
            ->where('tanggal', $today)
            ->whereNull('jam_keluar')
            ->update(['jam_keluar' => now()->toTimeString()]);

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

    // GET /api/absensi/today — Cek absensi hari ini (user)
    public function today(Request $request)
    {
        $authUser = $request->user();
        $today    = now()->toDateString();

        $data = Absensi::where('user_id', $authUser->id)
            ->where('tanggal', $today)
            ->first();

        return response()->json(['data' => $data]);
    }

    // GET /api/absensi/riwayat — Riwayat absensi milik user
    public function riwayat(Request $request)
    {
        $authUser = $request->user();
        $limit    = $request->get('limit', 30);

        $data = Absensi::where('user_id', $authUser->id)
            ->orderByDesc('tanggal')
            ->limit($limit)
            ->get();

        return response()->json(['data' => $data]);
    }

    // GET /api/absensi/laporan — Laporan bulanan user
    public function laporan(Request $request)
    {
        $authUser = $request->user();
        $bulan    = $request->bulan ?? now()->month;
        $tahun    = $request->tahun ?? now()->year;

        $summary = Absensi::where('user_id', $authUser->id)
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

        $detail = Absensi::where('user_id', $authUser->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderByDesc('tanggal')
            ->get();

        return response()->json([
            'summary' => $summary,
            'detail'  => $detail,
        ]);
    }

    // PUT /api/absensi/{id} — Update absensi (admin)
    public function update(Request $request, int $id)
    {
        $request->validate([
            'status'     => ['required', Rule::in(AbsensiStatus::adminFormValues())],
            'jam_masuk'  => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
        ]);

        Absensi::where('id', $id)->update([
            'user_id'    => $request->user_id,
            'tanggal'    => $request->tanggal,
            'status'     => $request->status,
            'jam_masuk'  => $request->jam_masuk ?: null,
            'jam_keluar' => $request->jam_keluar ?: null,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(['message' => 'Absensi berhasil diupdate.']);
    }

    // DELETE /api/absensi/{id} — Hapus absensi (admin)
    public function destroy(int $id)
    {
        Absensi::where('id', $id)->delete();
        return response()->json(['message' => 'Absensi berhasil dihapus.']);
    }
}
