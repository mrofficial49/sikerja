<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceEvidenceController extends Controller
{
    /**
     * Menampilkan halaman detail bukti presensi.
     */
    public function show(
        Request $request,
        Attendance $attendance
    ): View {
        /*
         * Memuat data Personel, unit, dan jadwal
         * yang berkaitan dengan presensi.
         */
        $attendance->loadMissing([
            'scheduleMember.user.unit',
            'scheduleMember.schedule',
        ]);

        /*
         * Pastikan pengguna memiliki hak akses
         * terhadap presensi tersebut.
         */
        $this->authorizeAttendance(
            $request,
            $attendance
        );

        return view(
            'attendance.evidence',
            compact('attendance')
        );
    }

    /**
     * Mengirim foto check-in atau check-out
     * dari storage privat.
     */
    public function photo(
        Request $request,
        Attendance $attendance,
        string $type
    ): StreamedResponse {
        /*
         * Hanya dua jenis foto yang diperbolehkan.
         */
        if (! in_array(
            $type,
            ['checkin', 'checkout'],
            true
        )) {
            abort(404);
        }

        $attendance->loadMissing([
            'scheduleMember.user',
        ]);

        $this->authorizeAttendance(
            $request,
            $attendance
        );

        /*
         * Menentukan kolom database berdasarkan
         * jenis foto yang diminta.
         */
        $photoPathColumn =
            $type.'_photo_path';

        $deletedAtColumn =
            $type.'_photo_deleted_at';

        $photoPath =
            $attendance->{$photoPathColumn};

        $deletedAt =
            $attendance->{$deletedAtColumn};

        /*
         * Foto tidak boleh ditampilkan apabila:
         * 1. Path foto kosong.
         * 2. Foto sudah ditandai terhapus.
         * 3. File tidak ditemukan pada storage.
         */
        if (
            blank($photoPath)
            || $deletedAt
            || ! Storage::disk('local')
                ->exists($photoPath)
        ) {
            abort(
                404,
                'Foto presensi tidak tersedia.'
            );
        }

        /*
         * Membaca tipe file sebenarnya.
         */
        $mimeType = Storage::disk('local')
            ->mimeType($photoPath)
            ?: 'image/jpeg';

        /*
         * Mengirim foto tanpa membuat file
         * menjadi publik.
         */
        return Storage::disk('local')->response(
            $photoPath,
            basename($photoPath),
            [
                'Content-Type' => $mimeType,

                /*
                 * Foto presensi tidak disimpan
                 * pada cache publik browser.
                 */
                'Cache-Control' =>
                    'private, no-store, max-age=0',

                'Pragma' => 'no-cache',
            ]
        );
    }

    /**
     * Memeriksa hak akses pengguna.
     */
    private function authorizeAttendance(
        Request $request,
        Attendance $attendance
    ): void {
        $user = $request
            ->user()
            ->loadMissing('role');

        $roleName = $user->role?->name;

        /*
         * Admin dan Pimpinan boleh melihat
         * seluruh bukti presensi.
         */
        if (in_array(
            $roleName,
            ['Admin', 'Pimpinan'],
            true
        )) {
            return;
        }

        /*
         * Personel hanya boleh melihat
         * bukti presensi miliknya sendiri.
         */
        if (
            $roleName === 'Personel'
            && (int) $attendance
                ->scheduleMember
                ?->user_id
                === (int) $user->id
        ) {
            return;
        }

        abort(
            403,
            'Anda tidak memiliki hak akses terhadap bukti presensi ini.'
        );
    }
}
