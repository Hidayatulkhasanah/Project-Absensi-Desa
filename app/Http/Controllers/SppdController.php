<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SppdController extends Controller
{
    public function index()
    {
        $sppd = DB::table('sppd')
            ->join('users', 'users.id', '=', 'sppd.user_id')
            ->select('sppd.*', 'users.nama', 'users.jabatan')
            ->orderBy('sppd.created_at', 'desc')
            ->get();

        return response()->json($sppd);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'nomor_sppd'        => 'required|unique:sppd',
            'tujuan'            => 'required',
            'keperluan'         => 'required',
            'tanggal_berangkat' => 'required|date',
            'tanggal_kembali'   => 'required|date|after_or_equal:tanggal_berangkat',
        ]);

        $id = DB::table('sppd')->insertGetId([
            'user_id'           => $request->user_id,
            'nomor_sppd'        => $request->nomor_sppd,
            'tujuan'            => $request->tujuan,
            'keperluan'         => $request->keperluan,
            'tanggal_berangkat' => $request->tanggal_berangkat,
            'tanggal_kembali'   => $request->tanggal_kembali,
            'status'            => 'menunggu',
            'keterangan'        => $request->keterangan,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json([
            'message' => 'SPPD berhasil dibuat.',
            'id'      => $id
        ], 201);
    }

    public function verifikasi(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
        ]);

        DB::table('sppd')->where('id', $id)->update([
            'status'     => $request->status,
            'keterangan' => $request->keterangan,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'SPPD berhasil diverifikasi.']);
    }

    public function destroy(int $id)
    {
        DB::table('sppd')->where('id', $id)->delete();
        return response()->json(['message' => 'SPPD berhasil dihapus.']);
    }
}