<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Memastikan akun yang sedang login masih aktif.
     *
     * Middleware ini penting apabila Admin menonaktifkan akun
     * ketika pengguna tersebut masih memiliki session login.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        // Jika pengguna tidak ditemukan atau sudah dinonaktifkan,
        // keluarkan pengguna dari sistem.
        if (! $user || ! $user->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'login_id' => 'Akun Anda sudah tidak aktif. Hubungi Admin SIKERJA.',
                ]);
        }

        return $next($request);
    }
}
