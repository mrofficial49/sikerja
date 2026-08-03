<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * Menampilkan halaman perubahan password.
     */
    public function edit(Request $request): View
    {
        return view('auth.change-password', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Menyimpan password baru pengguna.
     */
    public function update(Request $request): RedirectResponse
    {
        /*
         * current_password:
         * Memastikan password lama sesuai.
         *
         * confirmed:
         * Memastikan field password_confirmation sama
         * dengan field password.
         */
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'confirmed',

                // Minimal 8 karakter, huruf besar-kecil,
                // angka, dan simbol.
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'current_password.required' =>
                'Password sementara atau password lama wajib diisi.',

            'current_password.current_password' =>
                'Password sementara atau password lama tidak sesuai.',

            'password.required' =>
                'Password baru wajib diisi.',

            'password.confirmed' =>
                'Konfirmasi password baru tidak sesuai.',
        ]);

        $user = $request->user();

        /*
         * Model User menggunakan cast "hashed",
         * sehingga password otomatis di-hash saat disimpan.
         */
        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        // Mencatat aktivitas perubahan password.
        DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'action' => 'password_changed',
            'description' => 'Pengguna berhasil mengganti password.',
            'subject_type' => 'User',
            'subject_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Password berhasil diperbarui.');
    }
}
