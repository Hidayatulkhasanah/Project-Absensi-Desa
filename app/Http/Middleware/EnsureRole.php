<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * Authorization only — authentication is handled by the auth:sanctum
     * guard, which must run before this middleware so $request->user() is
     * already populated.
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if ($role === UserRole::Admin->value && $user->role !== UserRole::Admin) {
            return response()->json([
                'error' => 'Akses ditolak. Hanya admin yang diizinkan.'
            ], 403);
        }

        if ($role === UserRole::Operator->value && !in_array($user->role, [UserRole::Admin, UserRole::Operator], true)) {
            return response()->json([
                'error' => 'Akses ditolak. Hanya operator dan admin yang diizinkan.'
            ], 403);
        }

        return $next($request);
    }
}
