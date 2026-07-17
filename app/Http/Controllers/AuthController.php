<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        // One active session at a time, matching the previous custom-token behavior.
        $user->tokens()->delete();

        $token = $user->createToken('api-token', ['*'], now()->addHours(8))->plainTextToken;

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
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function profile(Request $request) {
        $user = $request->user();
        return response()->json(['user' => [
            'nik'     => $user->nik,
            'nama'    => $user->nama,
            'jabatan' => $user->jabatan,
            'role'    => $user->role,
        ]]);
    }

    public function changePassword(Request $request) {
        $request->validate([
            'password_lama' => 'required',
            'password_baru'  => 'required|min:6',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return response()->json(['error' => 'Password lama tidak sesuai.'], 400);
        }

        $user->password = Hash::make($request->password_baru);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}
