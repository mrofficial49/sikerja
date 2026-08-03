<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\SystemSetting;
use App\Models\WfhScheduleMember;
use App\Models\WorkReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AttendanceController extends Controller
{
    /**
     * Menampilkan halaman check-in Personel.
     */
    public function show(Request $request): View
    {
        $member = $this->findMembership(
            $request->user()->id
        );

        $testMode = $this->isTestMode();
        $now = now('Asia/Jakarta');

        $canCheckIn = false;
        $requiresLateReason = false;
        $message = 'Tidak ada jadwal WFH aktif untuk Anda.';

        if ($member) {
            $scheduleDate = Carbon::parse(
                $member->schedule->wfh_date->format('Y-m-d'),
                'Asia/Jakarta'
            );

            $checkinStart = $scheduleDate
                ->copy()
                ->setTime(7, 0, 0);

            /*
             * 07.10.59 masih dianggap tepat waktu.
             * Mulai 07.11 dianggap terlambat.
             */
            $onTimeEnd = $scheduleDate
                ->copy()
                ->setTime(7, 10, 59);

            $deadline = $member->checkin_deadline
                ?? $scheduleDate->copy()->setTime(8, 0, 0);

            if ($member->attendance?->checkin_at) {
                $message = 'Anda sudah melakukan check-in.';
            } elseif ($testMode) {
                $canCheckIn = true;
                $message = 'Mode pengujian aktif. Check-in dapat diuji sekarang.';
            } elseif (
                ! $member->is_schedule_change
                && $now->lessThan($checkinStart)
            ) {
                $message = 'Check-in dibuka pukul 07.00 WIB.';
            } elseif ($now->greaterThan($deadline)) {
                $message = 'Batas waktu check-in telah berakhir.';
            } else {
                $canCheckIn = true;

                /*
                 * Alasan terlambat hanya diwajibkan untuk
                 * jadwal normal setelah pukul 07.10.
                 *
                 * Perubahan jadwal memiliki jendela khusus
                 * selama 30 menit.
                 */
                $requiresLateReason =
                    ! $member->is_schedule_change
                    && $now->greaterThan($onTimeEnd);

                $message = $requiresLateReason
                    ? 'Anda terlambat. Alasan keterlambatan wajib diisi.'
                    : 'Silakan ambil foto dan lokasi untuk check-in.';
            }
        }

        return view('personnel.attendance.show', compact(
            'member',
            'testMode',
            'canCheckIn',
            'requiresLateReason',
            'message',
            'now'
        ));
    }

    /**
     * Menyimpan check-in Personel.
     */
    public function checkIn(
        Request $request,
        WfhScheduleMember $wfhScheduleMember
    ): JsonResponse {
        /*
         * Mencegah Personel melakukan check-in
         * menggunakan ID anggota milik orang lain.
         */
        if (
            $wfhScheduleMember->user_id
            !== $request->user()->id
        ) {
            abort(403);
        }

        $wfhScheduleMember->load([
            'schedule',
            'attendance',
        ]);

        if ($wfhScheduleMember->schedule->status !== 'active') {
            return $this->errorResponse(
                'Jadwal WFH tidak sedang aktif.'
            );
        }

        if (
            ! in_array(
                $wfhScheduleMember->member_status,
                ['scheduled', 'schedule_change', 'present'],
                true
            )
        ) {
            return $this->errorResponse(
                'Anda tidak dapat melakukan check-in pada jadwal ini.'
            );
        }

        if ($wfhScheduleMember->attendance?->checkin_at) {
            return $this->errorResponse(
                'Anda sudah melakukan check-in.'
            );
        }

        /*
         * Validasi tetap dilakukan di server.
         * Data dari JavaScript tidak langsung dipercaya.
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

            'late_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],

            /*
             * Foto maksimal 4 MB.
             * Format dibatasi ke JPEG, PNG, atau WEBP.
             */
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

            'late_reason.max' =>
                'Alasan keterlambatan maksimal 1.000 karakter.',

            'photo.required' =>
                'Foto kamera wajib diambil.',

            'photo.image' =>
                'File foto tidak valid.',

            'photo.mimes' =>
                'Foto harus berformat JPEG, PNG, atau WEBP.',

            'photo.max' =>
                'Ukuran foto maksimal 4 MB.',
        ]);

        $testMode = $this->isTestMode();
        $now = now('Asia/Jakarta');

        $scheduleDate = Carbon::parse(
            $wfhScheduleMember
                ->schedule
                ->wfh_date
                ->format('Y-m-d'),
            'Asia/Jakarta'
        );

        $checkinStart = $scheduleDate
            ->copy()
            ->setTime(7, 0, 0);

        $onTimeEnd = $scheduleDate
            ->copy()
            ->setTime(7, 10, 59);

        $deadline = $wfhScheduleMember->checkin_deadline
            ?? $scheduleDate->copy()->setTime(8, 0, 0);

        if (! $testMode) {
            /*
             * Jadwal hanya berlaku pada tanggal WFH.
             */
            if (! $scheduleDate->isSameDay($now)) {
                return $this->errorResponse(
                    'Check-in hanya dapat dilakukan pada tanggal pelaksanaan WFH.'
                );
            }

            /*
             * Jadwal normal dibuka pukul 07.00.
             * Perubahan jadwal mempunyai waktu khusus.
             */
            if (
                ! $wfhScheduleMember->is_schedule_change
                && $now->lessThan($checkinStart)
            ) {
                return $this->errorResponse(
                    'Check-in belum dibuka. Check-in dimulai pukul 07.00 WIB.'
                );
            }

            /*
             * Jika melewati deadline, status anggota
             * otomatis menjadi tidak hadir.
             */
            if ($now->greaterThan($deadline)) {
                DB::transaction(function () use (
                    $wfhScheduleMember
                ) {
                    $member = WfhScheduleMember::query()
                        ->lockForUpdate()
                        ->findOrFail($wfhScheduleMember->id);

                    if (! $member->attendance?->checkin_at) {
                        $member->update([
                            'member_status' => 'absent',
                        ]);

                        Attendance::updateOrCreate(
                            [
                                'schedule_member_id' => $member->id,
                            ],
                            [
                                'checkin_status' => 'missed',
                                'attendance_status' => 'absent',
                            ]
                        );
                    }
                });

                return $this->errorResponse(
                    'Batas waktu check-in telah berakhir. Status Anda menjadi tidak hadir.'
                );
            }
        }

        /*
         * Perubahan jadwal yang dilakukan setelah pukul 08.00
         * dianggap tepat waktu selama masih dalam jendela 30 menit.
         */
        $isLate = ! $testMode
            && ! $wfhScheduleMember->is_schedule_change
            && $now->greaterThan($onTimeEnd);

        if (
            $isLate
            && blank($validated['late_reason'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'late_reason' =>
                    'Alasan keterlambatan wajib diisi setelah pukul 07.10 WIB.',
            ]);
        }

        $checkinStatus = $isLate
            ? 'late'
            : 'on_time';

        $lateReason = $isLate
            ? trim($validated['late_reason'])
            : null;

        $photo = $request->file('photo');

        $filename = 'checkin_'
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
                $checkinStatus,
                $lateReason,
                &$storedPath
            ) {
                /*
                 * Lock mencegah dua request check-in
                 * diproses pada waktu yang sama.
                 */
                $member = WfhScheduleMember::query()
                    ->with('attendance')
                    ->lockForUpdate()
                    ->findOrFail($wfhScheduleMember->id);

                if ($member->attendance?->checkin_at) {
                    throw ValidationException::withMessages([
                        'photo' =>
                            'Check-in sudah pernah disimpan.',
                    ]);
                }

                /*
                 * Foto disimpan pada disk local.
                 * Disk local Laravel bersifat privat.
                 */
                $storedPath = $photo->storeAs(
                    'attendance/checkin/'
                        . $member->schedule_id,
                    $filename,
                    'local'
                );

                if (! $storedPath) {
                    throw new RuntimeException(
                        'Foto check-in gagal disimpan.'
                    );
                }

                $retentionDays = (int) SystemSetting::getValue(
                    'file_retention_days',
                    30
                );

                Attendance::updateOrCreate(
                    [
                        'schedule_member_id' => $member->id,
                    ],
                    [
                        'checkin_at' => $now,
                        'checkin_status' => $checkinStatus,
                        'checkin_reason' => $lateReason,

                        'checkin_latitude' =>
                            $validated['latitude'],

                        'checkin_longitude' =>
                            $validated['longitude'],

                        'checkin_photo_path' => $storedPath,

                        'checkin_photo_expires_at' =>
                            $now->copy()->addDays($retentionDays),

                        'checkin_photo_deleted_at' => null,

                        /*
                         * Presensi masih incomplete karena
                         * Personel belum melakukan check-out.
                         */
                        'attendance_status' => 'incomplete',
                    ]
                );

                $member->update([
                    'member_status' => 'present',
                ]);

                /*
                 * Setelah check-in, laporan kerja draft
                 * otomatis disiapkan.
                 */
                WorkReport::firstOrCreate(
                    [
                        'schedule_member_id' => $member->id,
                    ],
                    [
                        'status' => 'draft',
                        'is_locked' => false,
                    ]
                );

                ActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'attendance_checkin',
                    'description' =>
                        'Personel melakukan check-in WFH.',

                    'subject_type' => Attendance::class,

                    'subject_id' => Attendance::where(
                        'schedule_member_id',
                        $member->id
                    )->value('id'),

                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            });
        } catch (Throwable $exception) {
            /*
             * Hapus foto apabila transaksi database gagal,
             * sehingga tidak ada file yatim.
             */
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Check-in berhasil disimpan.',
            'redirect' => route('personnel.dashboard'),
        ]);
    }

    /**
     * Mencari jadwal aktif milik Personel.
     */
    private function findMembership(
        int $userId
    ): ?WfhScheduleMember {
        $query = WfhScheduleMember::query()
            ->with([
                'schedule',
                'attendance',
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

        /*
         * Mode normal hanya mengambil jadwal hari ini.
         */
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

        /*
         * Mode lokal mengambil jadwal aktif terbaru,
         * sehingga fitur dapat diuji di luar hari Jumat.
         */
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
     * Mode pengujian hanya boleh aktif pada local.
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
     * Response kesalahan untuk JavaScript.
     */
    private function errorResponse(
        string $message
    ): JsonResponse {
        return response()->json([
            'message' => $message,
        ], 422);
    }
}
