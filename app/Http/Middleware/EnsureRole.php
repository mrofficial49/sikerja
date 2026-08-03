<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Memastikan pengguna memiliki role yang diizinkan.
     *
     * Contoh penggunaan:
     * role:Admin
     * role:Pimpinan
     * role:Admin,Pimpinan
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$allowedRoles
    ): Response {
        $user = $request->user();

        // Memuat relasi role apabila belum dimuat.
        $user?->loadMissing('role');

        // Tolak akses jika role pengguna tidak termasuk
        // dalam daftar role yang diperbolehkan.
        if (
            ! $user ||
            ! $user->role ||
            ! in_array($user->role->name, $allowedRoles, true)
        ) {
            abort(
                403,
                'Anda tidak memiliki hak akses untuk membuka halaman ini.'
            );
        }

        return $next($request);
    }
}
