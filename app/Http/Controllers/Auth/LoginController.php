<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLogin(): View|RedirectResponse
    {
        // Pengguna yang sudah login langsung menuju dashboard.
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        /*
         * Mengambil pengaturan publik untuk ditampilkan
         * pada halaman login.
         */
        $settings = DB::table('system_settings')
            ->whereIn('setting_key', [
                'app_name',
                'app_subtitle',
                'admin_contact_name',
                'admin_contact_phone',
            ])
            ->pluck('setting_value', 'setting_key');

        return view('auth.login', compact('settings'));
    }

    /**
     * Memproses login pengguna.
     */
    public function login(Request $request): RedirectResponse
    {
        // Memeriksa data yang dikirim dari formulir.
        $validated = $request->validate([
            'login_id' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], [
            'login_id.required' => 'ID Login wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        /*
         * is_active = true mencegah akun nonaktif masuk.
         *
         * Parameter kedua bernilai true agar pengguna tetap
         * dikenali sampai melakukan logout secara manual.
         */
        $authenticated = Auth::attempt([
            'login_id' => trim($validated['login_id']),
            'password' => $validated['password'],
            'is_active' => true,
        ], true);

        if (! $authenticated) {
            // Mencatat kegagalan login ke activity log.
            DB::table('activity_logs')->insert([
                'user_id' => null,
                'action' => 'login_failed',
                'description' => 'Percobaan login gagal untuk ID: '
                    . trim($validated['login_id']),
                'subject_type' => 'User',
                'subject_id' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return back()
                ->withErrors([
                    'login_id' => 'ID Login atau password tidak sesuai.',
                ])
                ->onlyInput('login_id');
        }

        /*
         * Membuat ID session baru untuk melindungi
         * pengguna dari session fixation.
         */
        $request->session()->regenerate();

        $user = $request->user();

        // Menyimpan waktu login terakhir.
        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        // Mencatat login berhasil.
        DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'action' => 'login_success',
            'description' => 'Pengguna berhasil login.',
            'subject_type' => 'User',
            'subject_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        /*
         * Akun baru atau akun hasil reset wajib mengganti
         * password sebelum membuka dashboard.
         */
        if ($user->must_change_password) {
            return redirect()
                ->route('password.change')
                ->with(
                    'warning',
                    'Silakan ganti password sementara terlebih dahulu.'
                );
        }

        return redirect()
            ->intended(route('dashboard'))
            ->with('success', 'Selamat datang di SIKERJA.');
    }

    /**
     * Mengeluarkan pengguna dari aplikasi.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Catat aktivitas sebelum session dihapus.
        if ($user) {
            DB::table('activity_logs')->insert([
                'user_id' => $user->id,
                'action' => 'logout',
                'description' => 'Pengguna keluar dari aplikasi.',
                'subject_type' => 'User',
                'subject_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        }

        Auth::logout();

        // Menghapus session lama.
        $request->session()->invalidate();

        // Membuat token CSRF yang baru.
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil keluar.');
    }
}
