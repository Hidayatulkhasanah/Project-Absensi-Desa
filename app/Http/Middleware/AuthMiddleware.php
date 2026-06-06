<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next, ?string $role = null)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Token tidak ditemukan. Silakan login ulang.'
            ], 401);
        }

        $user = DB::table('sessions')
            ->join('users', 'users.id', '=', 'sessions.user_id')
            ->where('sessions.token', $token)
            ->where('sessions.expired_at', '>', now())
            ->where('users.aktif', 1)
            ->select('sessions.user_id', 'users.nik', 'users.nama', 'users.jabatan', 'users.role')
            ->first();

        if (!$user) {
            return response()->json([
                'error' => 'Sesi habis atau tidak valid. Silakan login ulang.'
            ], 401);
        }

        // Cek role admin
        if ($role === 'admin' && $user->role !== 'admin') {
            return response()->json([
                'error' => 'Akses ditolak. Hanya admin yang diizinkan.'
            ], 403);
        }

        // Cek role operator (admin juga boleh akses)
        if ($role === 'operator' && !in_array($user->role, ['admin', 'operator'])) {
            return response()->json([
                'error' => 'Akses ditolak. Hanya operator dan admin yang diizinkan.'
            ], 403);
        }

        $request->attributes->set('auth_user', $user);
        return $next($request);
    }
}