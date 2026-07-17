<?php

namespace App\Http\Controllers;

use App\Enums\SppdStatus;
use App\Enums\UserRole;
use App\Models\Sppd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SppdController extends Controller
{
    /**
     * GET /api/sppd
     * List SPPD. Admin/operator melihat semua, user biasa hanya melihat miliknya sendiri.
     * ?paginate=1 returns Laravel's paginator shape for the Kelola SPPD table;
     * other callers (badges, verify-by-id lookup) rely on the full unpaginated
     * list, so pagination stays strictly opt-in.
     */
    public function index(Request $request)
    {
        $authUser = $request->user();

        $query = Sppd::query()
            ->join('users', 'users.id', '=', 'sppd.user_id')
            ->select('sppd.*', 'users.nama', 'users.jabatan', 'users.nik')
            ->orderByDesc('sppd.created_at');

        if (!in_array($authUser->role, [UserRole::Admin, UserRole::Operator], true)) {
            $query->where('sppd.user_id', $authUser->id);
        }

        if ($request->boolean('paginate')) {
            return response()->json($query->paginate($request->integer('per_page', 15))->withQueryString());
        }

        return response()->json(['data' => $query->get()]);
    }

    /**
     * GET /api/sppd/{id}
     */
    public function show(Request $request, int $id)
    {
        $authUser = $request->user();
        $sppd = Sppd::findOrFail($id);

        if (!in_array($authUser->role, [UserRole::Admin, UserRole::Operator], true) && $sppd->user_id !== $authUser->id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        return response()->json(['data' => $sppd]);
    }

    /**
     * POST /api/sppd
     * File dikirim sebagai base64 (field: file_sppd_base64, file_sppd_name), konsisten dengan pola foto_base64 di absensi.
     */
    public function store(Request $request)
    {
        $authUser = $request->user();

        $validatedData = $request->validate([
            'nomor_sppd' => 'required|unique:sppd',
            'tujuan' => 'required|string',
            'keperluan' => 'required|string',
            'tanggal_berangkat' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'file_sppd_base64' => 'required|string',
            'file_sppd_name' => 'required|string',
        ]);

        $filePath = $this->saveBase64File($validatedData['file_sppd_base64'], $validatedData['file_sppd_name']);
        if ($filePath === null) {
            return response()->json(['error' => 'File tidak valid atau format tidak didukung (hanya PDF/JPG/PNG, maks 5MB).'], 422);
        }

        $sppd = new Sppd();
        $sppd->user_id = $authUser->id;
        $sppd->nomor_sppd = $validatedData['nomor_sppd'];
        $sppd->tujuan = $validatedData['tujuan'];
        $sppd->keperluan = $validatedData['keperluan'];
        $sppd->tanggal_berangkat = $validatedData['tanggal_berangkat'];
        $sppd->tanggal_kembali = $validatedData['tanggal_kembali'];
        $sppd->file_sppd = $filePath;
        $sppd->save();

        return response()->json([
            'message' => 'Pengajuan SPPD berhasil disimpan.',
            'data' => $sppd,
        ]);
    }

    /**
     * PUT /api/sppd/{id}
     */
    public function update(Request $request, int $id)
    {
        $authUser = $request->user();
        $sppd = Sppd::findOrFail($id);

        if (!in_array($authUser->role, [UserRole::Admin, UserRole::Operator], true) && $sppd->user_id !== $authUser->id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        if ($sppd->status !== SppdStatus::Menunggu && !in_array($authUser->role, [UserRole::Admin, UserRole::Operator], true)) {
            return response()->json(['error' => 'SPPD yang sudah diverifikasi tidak dapat diubah.'], 422);
        }

        $validatedData = $request->validate([
            'nomor_sppd' => 'required|unique:sppd,nomor_sppd,' . $id,
            'tujuan' => 'required|string',
            'keperluan' => 'required|string',
            'tanggal_berangkat' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'file_sppd_base64' => 'nullable|string',
            'file_sppd_name' => 'nullable|string',
        ]);

        $sppd->nomor_sppd = $validatedData['nomor_sppd'];
        $sppd->tujuan = $validatedData['tujuan'];
        $sppd->keperluan = $validatedData['keperluan'];
        $sppd->tanggal_berangkat = $validatedData['tanggal_berangkat'];
        $sppd->tanggal_kembali = $validatedData['tanggal_kembali'];

        if (!empty($validatedData['file_sppd_base64'])) {
            $filePath = $this->saveBase64File($validatedData['file_sppd_base64'], $validatedData['file_sppd_name'] ?? 'file');
            if ($filePath === null) {
                return response()->json(['error' => 'File tidak valid atau format tidak didukung (hanya PDF/JPG/PNG, maks 5MB).'], 422);
            }
            if ($sppd->file_sppd) {
                Storage::disk('public')->delete($sppd->file_sppd);
            }
            $sppd->file_sppd = $filePath;
        }

        $sppd->save();

        return response()->json([
            'message' => 'Pengajuan SPPD berhasil diperbarui.',
            'data' => $sppd,
        ]);
    }

    /**
     * DELETE /api/sppd/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $authUser = $request->user();
        $sppd = Sppd::findOrFail($id);

        if (!in_array($authUser->role, [UserRole::Admin, UserRole::Operator], true) && $sppd->user_id !== $authUser->id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        if ($sppd->status !== SppdStatus::Menunggu && !in_array($authUser->role, [UserRole::Admin, UserRole::Operator], true)) {
            return response()->json(['error' => 'SPPD yang sudah diverifikasi tidak dapat dihapus.'], 422);
        }

        if ($sppd->file_sppd) {
            Storage::disk('public')->delete($sppd->file_sppd);
        }
        $sppd->delete();

        return response()->json(['message' => 'Pengajuan SPPD berhasil dihapus.']);
    }

    /**
     * PUT /api/sppd/{id}/verifikasi  (khusus admin/operator: setujui/tolak)
     */
    public function verifikasi(Request $request, int $id)
    {
        $authUser = $request->user();

        if (!in_array($authUser->role, [UserRole::Admin, UserRole::Operator], true)) {
            return response()->json(['error' => 'Akses ditolak. Hanya admin/operator yang dapat mengubah status.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::enum(SppdStatus::class)],
            'keterangan' => 'nullable|string',
        ]);

        $sppd = Sppd::findOrFail($id);
        $sppd->status = $validated['status'];
        $sppd->keterangan = $validated['keterangan'] ?? $sppd->keterangan;
        $sppd->save();

        return response()->json([
            'message' => 'Status SPPD berhasil diperbarui.',
            'data' => $sppd,
        ]);
    }

    /**
     * Decode base64 file, validasi tipe & ukuran, simpan ke storage/app/public/sppd.
     * Return path relatif (untuk Storage::url) atau null kalau tidak valid.
     */
    private function saveBase64File(string $base64, string $originalName): ?string
    {
        if (preg_match('/^data:(.*?);base64,(.*)$/', $base64, $matches)) {
            $mime = $matches[1];
            $data = base64_decode($matches[2]);
        } else {
            $data = base64_decode($base64);
            $mime = null;
        }

        if ($data === false) {
            return null;
        }

        // Maks 2MB
        if (strlen($data) > 5 * 1024 * 1024) {
            return null;
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowedExt)) {
            return null;
        }

        $fileName = time() . '_' . Str::random(8) . '.' . $ext;
        $path = 'sppd/' . $fileName;

        Storage::disk('public')->put($path, $data);

        return $path;
    }
}
