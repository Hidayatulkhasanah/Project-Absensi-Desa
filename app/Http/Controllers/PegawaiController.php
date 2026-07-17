<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class PegawaiController extends Controller
{
    // GET /api/pegawai
    // ?paginate=1 returns Laravel's paginator shape for the Data Pegawai table;
    // other callers (dropdowns, badges, settings counts) rely on the full
    // unpaginated list, so pagination stays strictly opt-in.
    public function index(Request $request)
    {
        $query = User::where('aktif', 1)
            ->select('id', 'nik', 'nama', 'jabatan', 'role', 'aktif')
            ->orderBy('nama');

        if ($request->boolean('paginate')) {
            return response()->json($query->paginate($request->integer('per_page', 15))->withQueryString());
        }

        return response()->json(['data' => $query->get()]);
    }

    // POST /api/pegawai
    public function store(Request $request)
    {
        $request->validate([
            'nik'      => 'required|unique:users',
            'nama'     => 'required',
            'password' => 'required|min:6',
            'jabatan'  => 'nullable',
            'role'     => ['required', Rule::in(UserRole::pegawaiFormValues())],
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
            'role'    => ['required', Rule::in(UserRole::pegawaiFormValues())],
            'aktif'   => 'required|in:0,1',
        ]);

        $data = [
            'nama'    => $request->nama,
            'jabatan' => $request->jabatan,
            'role'    => $request->role,
            'aktif'   => $request->aktif,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        User::where('id', $id)->update($data);

        return response()->json(['message' => 'Pegawai berhasil diupdate.']);
    }

    // DELETE /api/pegawai/{id} — nonaktifkan
    public function destroy(int $id)
    {
        User::where('id', $id)->update(['aktif' => 0]);
        return response()->json(['message' => 'Pegawai berhasil dinonaktifkan.']);
    }
}