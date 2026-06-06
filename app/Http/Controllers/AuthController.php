<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request) {
        $request->validate([
            'nik'      => 'required',
            'password' => 'required',
        ]);

        $user = User::where('nik', $request->nik)
                    ->where('aktif', 1)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'NIK atau password salah.'], 401);
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();

        $token = Str::random(64);
        DB::table('sessions')->insert([
            'user_id'    => $user->id,
            'token'      => $token,
            'expired_at' => now()->addHours(8),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'nik'     => $user->nik,
                'nama'    => $user->nama,
                'jabatan' => $user->jabatan,
                'role'    => $user->role,
            ]
        ]);
    }

    public function logout(Request $request) {
        DB::table('sessions')->where('token', $request->bearerToken())->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function profile(Request $request) {
        return response()->json(['user' => $request->attributes->get('auth_user')]);
    }

    public function changePassword(Request $request) {
        $request->validate([
            'password_lama' => 'required',
            'password_baru'  => 'required|min:6',
        ]);

        $authUser = $request->attributes->get('auth_user');
        $user = User::find($authUser->id);

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan.'], 404);
        }

        if (!Hash::check($request->password_lama, $user->password)) {
            return response()->json(['error' => 'Password lama tidak sesuai.'], 400);
        }

        $user->password = Hash::make($request->password_baru);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}