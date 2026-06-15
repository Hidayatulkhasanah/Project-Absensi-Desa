<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PegawaiController extends Controller
{
    // GET /api/pegawai
    public function index()
    {
        $pegawai = User::where('aktif', 1)
            ->select('id', 'nik', 'nama', 'jabatan', 'role', 'aktif')
            ->orderBy('nama')
            ->get();

        return response()->json(['data' => $pegawai]);
    }

    // POST /api/pegawai
    public function store(Request $request)
    {
        $request->validate([
            'nik'      => 'required|unique:users',
            'nama'     => 'required',
            'password' => 'required|min:6',
            'jabatan'  => 'nullable',
            'role'     => 'required|in:admin,user',
        ]);

        $user = User::create([
            'nik'      => $request->nik,
            'nama'     => $request->nama,
            'password' => Hash::make($request->password),
            'jabatan'  => $request->jabatan,
            'role'     => $request->role,
            'aktif'    => 1,
        ]);

        return response()->json([
            'message' => 'Pegawai berhasil ditambahkan.',
            'id'      => $user->id
        ], 201);
    }

    // PUT /api/pegawai/{id}
    public function update(Request $request, int $id)
    {
        $request->validate([
            'nama'    => 'required',
            'jabatan' => 'nullable',
            'role'    => 'required|in:admin,user',
            'aktif'   => 'required|in:0,1',
        ]);

        $data = [
            'nama'       => $request->nama,
            'jabatan'    => $request->jabatan,
            'role'       => $request->role,
            'aktif'      => $request->aktif,
            'updated_at' => now(),
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->update($data);

        return response()->json(['message' => 'Pegawai berhasil diupdate.']);
    }

    // DELETE /api/pegawai/{id} — nonaktifkan
    public function destroy(int $id)
    {
        DB::table('users')->where('id', $id)->update([
            'aktif'      => 0,
            'updated_at' => now(),
        ]);
        return response()->json(['message' => 'Pegawai berhasil dinonaktifkan.']);
    }
}