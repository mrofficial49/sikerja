<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Mencegah pengguna membuka halaman utama sebelum
     * mengganti password sementara.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        /*
         * Jika must_change_password masih true,
         * pengguna dialihkan ke halaman ganti password.
         */
        if ($user && $user->must_change_password) {
            return redirect()
                ->route('password.change')
                ->with(
                    'warning',
                    'Silakan ganti password sementara terlebih dahulu.'
                );
        }

        return $next($request);
    }
}
