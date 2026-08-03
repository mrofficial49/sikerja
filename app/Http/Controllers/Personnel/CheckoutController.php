<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\SystemSetting;
use App\Models\WorkReport;
use App\Models\WfhScheduleMember;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Menampilkan halaman check-out Personel.
     */
    public function show(
        Request $request
    ): View|RedirectResponse {
        $membership = $this->findCurrentMembership(
            $request->user()->id
        );

        if (! $membership) {
            return redirect()
                ->route('personnel.dashboard')
                ->with(
                    'error',
                    'Tidak ada jadwal WFH aktif untuk Anda.'
                );
        }

        /*
         * Personel wajib check-in sebelum check-out.
         */
        if (! $membership->attendance?->checkin_at) {
            return redirect()
                ->route('personnel.attendance.show')
                ->with(
                    'error',
                    'Silakan melakukan check-in terlebih dahulu.'
                );
        }

        $report = WorkReport::query()
            ->where(
                'schedule_member_id',
                $membership->id
            )
            ->first();

        if (! $report) {
            return redirect()
                ->route('personnel.report.show')
                ->with(
                    'error',
                    'Laporan kerja belum tersedia.'
                );
        }

        /*
         * Laporan harus dikirim terlebih dahulu
         * sebelum Personel dapat check-out.
         */
        if (
            ! $membership->attendance?->checkout_at
            && $report->status !== 'waiting_verification'
        ) {
            return redirect()
                ->route('personnel.report.show')
                ->with(
                    'error',
                    'Kirim laporan kerja sebelum melakukan check-out.'
                );
        }

        $testMode = $this->isTestMode();
        $now = now('Asia/Jakarta');

        /*
         * Waktu awal check-out.
         * Jika pengaturan belum tersedia, gunakan pukul 15.30.
         */
        $checkoutStartTime = SystemSetting::getValue(
            'checkout_start_time',
            '15:30'
        );

        $scheduleDate = Carbon::parse(
            $membership->schedule->wfh_date->format('Y-m-d'),
            'Asia/Jakarta'
        );

        $checkoutStart = Carbon::parse(
            $scheduleDate->format('Y-m-d')
                . ' '
                . $checkoutStartTime,
            'Asia/Jakarta'
        );

        $canCheckout = false;
        $message = 'Check-out belum tersedia.';

        if ($membership->attendance?->checkout_at) {
            $message = 'Anda sudah melakukan check-out.';
        } elseif ($testMode) {
            $canCheckout = true;

            $message =
                'Mode pengujian aktif. Check-out dapat diuji sekarang.';
        } elseif (! $scheduleDate->isSameDay($now)) {
            $message =
                'Check-out hanya dapat dilakukan pada tanggal WFH.';
        } elseif ($now->lessThan($checkoutStart)) {
            $message =
                'Check-out dibuka pukul '
                . $checkoutStart->format('H:i')
                . ' WIB.';
        } else {
            $canCheckout = true;

            $message =
                'Silakan mengambil foto dan lokasi untuk check-out.';
        }

        return view(
            'personnel.attendance.checkout',
            compact(
                'membership',
                'report',
                'testMode',
                'canCheckout',
                'message',
                'checkoutStart'
            )
        );
    }

    /**
     * Menyimpan data check-out Personel.
     */
    public function store(
        Request $request,
        WfhScheduleMember $wfhScheduleMember
    ): JsonResponse {
        /*
         * Personel hanya boleh memakai data jadwal miliknya.
         */
        if (
            (int) $wfhScheduleMember->user_id
            !== (int) $request->user()->id
        ) {
            abort(403);
        }

        $wfhScheduleMember->load([
            'schedule',
            'attendance',
            'workReport',
        ]);

        if (! $wfhScheduleMember->attendance?->checkin_at) {
            return $this->errorResponse(
                'Anda belum melakukan check-in.'
            );
        }

        if ($wfhScheduleMember->attendance?->checkout_at) {
            return $this->errorResponse(
                'Anda sudah melakukan check-out.'
            );
        }

        if (
            $wfhScheduleMember->workReport?->status
            !== 'waiting_verification'
        ) {
            return $this->errorResponse(
                'Laporan kerja harus dikirim sebelum check-out.'
            );
        }

        /*
         * Validasi foto dan koordinat GPS.
         */
        $validated = $request->validate([
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:4096',
            ],
        ], [
            'latitude.required' =>
                'Lokasi GPS belum diperoleh.',

            'latitude.between' =>
                'Koordinat latitude tidak valid.',

            'longitude.required' =>
                'Lokasi GPS belum diperoleh.',

            'longitude.between' =>
                'Koordinat longitude tidak valid.',

            'photo.required' =>
                'Foto check-out wajib diambil.',

            'photo.image' =>
                'Foto check-out tidak valid.',

            'photo.mimes' =>
                'Foto harus berformat JPEG, PNG, atau WEBP.',

            'photo.max' =>
                'Ukuran foto maksimal 4 MB.',
        ]);

        $now = now('Asia/Jakarta');
        $testMode = $this->isTestMode();

        $scheduleDate = Carbon::parse(
            $wfhScheduleMember
                ->schedule
                ->wfh_date
                ->format('Y-m-d'),
            'Asia/Jakarta'
        );

        $checkoutStartTime = SystemSetting::getValue(
            'checkout_start_time',
            '15:30'
        );

        $checkoutStart = Carbon::parse(
            $scheduleDate->format('Y-m-d')
                . ' '
                . $checkoutStartTime,
            'Asia/Jakarta'
        );

        /*
         * Aturan waktu tidak diterapkan dalam mode pengujian.
         */
        if (! $testMode) {
            if (! $scheduleDate->isSameDay($now)) {
                return $this->errorResponse(
                    'Check-out hanya dapat dilakukan pada tanggal WFH.'
                );
            }

            if ($now->lessThan($checkoutStart)) {
                return $this->errorResponse(
                    'Check-out belum dibuka.'
                );
            }
        }

        $photo = $request->file('photo');

        $filename = 'checkout_'
            . $wfhScheduleMember->id
            . '_'
            . $now->format('Ymd_His')
            . '_'
            . Str::uuid()
            . '.jpg';

        $storedPath = null;

        try {
            DB::transaction(function () use (
                $request,
                $wfhScheduleMember,
                $validated,
                $photo,
                $filename,
                $now,
                &$storedPath
            ) {
                /*
                 * Mengunci data agar request check-out
                 * tidak diproses dua kali.
                 */
                $member = WfhScheduleMember::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $wfhScheduleMember->id
                    );

                $attendance = Attendance::query()
                    ->where(
                        'schedule_member_id',
                        $member->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $report = WorkReport::query()
                    ->where(
                        'schedule_member_id',
                        $member->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($attendance->checkout_at) {
                    throw new RuntimeException(
                        'Check-out sudah pernah disimpan.'
                    );
                }

                if (
                    $report->status
                    !== 'waiting_verification'
                ) {
                    throw new RuntimeException(
                        'Laporan kerja belum dikirim.'
                    );
                }

                /*
                 * Simpan foto pada storage privat Laravel.
                 */
                $storedPath = $photo->storeAs(
                    'attendance/checkout/'
                        . $member->schedule_id,
                    $filename,
                    'local'
                );

                if (! $storedPath) {
                    throw new RuntimeException(
                        'Foto check-out gagal disimpan.'
                    );
                }

                $retentionDays = (int) SystemSetting::getValue(
                    'file_retention_days',
                    30
                );

                $attendance->forceFill([
                    'checkout_at' => $now,

                    'checkout_latitude' =>
                        $validated['latitude'],

                    'checkout_longitude' =>
                        $validated['longitude'],

                    'checkout_photo_path' =>
                        $storedPath,

                    'checkout_photo_expires_at' =>
                        $now->copy()->addDays(
                            $retentionDays
                        ),

                    'checkout_photo_deleted_at' => null,

                    /*
                     * Presensi dinyatakan selesai
                     * setelah check-out berhasil.
                     */
                    'attendance_status' => 'present',
                ])->save();

                /*
                 * Kunci laporan agar tidak dapat diubah lagi.
                 */
                $report->forceFill([
                    'is_locked' => true,
                ])->save();

                ActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'attendance_checkout',
                    'description' =>
                        'Personel melakukan check-out WFH.',
                    'subject_type' => Attendance::class,
                    'subject_id' => $attendance->id,
                    'ip_address' => $request->ip(),
                    'user_agent' =>
                        $request->userAgent(),
                ]);
            });
        } catch (Throwable $exception) {
            /*
             * Hapus foto jika transaksi database gagal.
             */
            if ($storedPath) {
                Storage::disk('local')->delete(
                    $storedPath
                );
            }

            throw $exception;
        }

        return response()->json([
            'message' =>
                'Check-out berhasil. Laporan telah dikunci.',

            'redirect' =>
                route('personnel.dashboard'),
        ]);
    }

    /**
     * Mencari jadwal aktif milik Personel.
     */
    private function findCurrentMembership(
        int $userId
    ): ?WfhScheduleMember {
        $query = WfhScheduleMember::query()
            ->with([
                'schedule',
                'attendance',
                'workReport',
            ])
            ->where('user_id', $userId)
            ->whereIn('member_status', [
                'scheduled',
                'schedule_change',
                'present',
            ])
            ->whereHas('schedule', function ($query) {
                $query->where('status', 'active');
            });

        if (! $this->isTestMode()) {
            return $query
                ->whereHas('schedule', function ($query) {
                    $query->whereDate(
                        'wfh_date',
                        now('Asia/Jakarta')->toDateString()
                    );
                })
                ->first();
        }

        return $query
            ->get()
            ->sortByDesc(function ($member) {
                return $member
                    ->schedule
                    ?->wfh_date
                    ?->timestamp ?? 0;
            })
            ->first();
    }

    /**
     * Mode pengujian hanya aktif pada environment local.
     */
    private function isTestMode(): bool
    {
        return app()->environment('local')
            && filter_var(
                config('sikerja.attendance_test_mode'),
                FILTER_VALIDATE_BOOLEAN
            );
    }

    /**
     * Membuat response error untuk JavaScript.
     */
    private function errorResponse(
        string $message
    ): JsonResponse {
        return response()->json([
            'message' => $message,
        ], 422);
    }
}
