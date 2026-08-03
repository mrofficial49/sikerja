<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\User;
use App\Models\WfhSchedule;
use App\Models\WfhScheduleMember;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WfhScheduleMemberController extends Controller
{
    /**
     * Menambahkan personel ke dalam jadwal WFH.
     *
     * Apabila personel ditambahkan pada hari Jumat setelah
     * pukul 08.00, sistem memberi waktu check-in selama 30 menit.
     */
    public function store(
        Request $request,
        WfhSchedule $wfhSchedule
    ): RedirectResponse {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'change_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'user_id.required' => 'Personel wajib dipilih.',
            'user_id.exists' => 'Personel yang dipilih tidak ditemukan.',
            'change_reason.max' =>
                'Alasan perubahan maksimal 1.000 karakter.',
        ]);

        /*
         * Anggota hanya dapat ditambahkan ke jadwal
         * yang masih draft atau aktif.
         */
        if (
            ! in_array(
                $wfhSchedule->status,
                ['draft', 'active'],
                true
            )
        ) {
            return back()->with(
                'error',
                'Anggota tidak dapat ditambahkan ke jadwal tersebut.'
            );
        }

        /*
         * Memastikan akun yang dipilih:
         * - masih aktif;
         * - memiliki role Personel.
         */
        $person = User::query()
            ->whereKey($validated['user_id'])
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('name', 'Personel');
            })
            ->first();

        if (! $person) {
            throw ValidationException::withMessages([
                'user_id' =>
                    'Akun harus aktif dan memiliki role Personel.',
            ]);
        }

        $now = now('Asia/Jakarta');

        $scheduleDate = Carbon::parse(
            $wfhSchedule->wfh_date->format('Y-m-d'),
            'Asia/Jakarta'
        )->startOfDay();

        /*
         * Anggota tidak dapat ditambahkan ke jadwal lampau.
         */
        if ($scheduleDate->isBefore($now->copy()->startOfDay())) {
            return back()->with(
                'error',
                'Personel tidak dapat ditambahkan ke jadwal lampau.'
            );
        }

        /*
         * Batas check-in normal adalah pukul 08.00.
         */
        $normalDeadline = $scheduleDate
            ->copy()
            ->setTime(8, 0, 0);

        /*
         * Perubahan jadwal berlaku apabila Admin menambahkan
         * personel pada tanggal pelaksanaan setelah pukul 08.00.
         */
        $isScheduleChange = $scheduleDate->isSameDay($now)
            && $now->greaterThan($normalDeadline);

        /*
         * Alasan wajib diisi untuk perubahan jadwal
         * setelah pukul 08.00.
         */
        if (
            $isScheduleChange
            && blank($validated['change_reason'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'change_reason' =>
                    'Alasan perubahan jadwal wajib diisi setelah pukul 08.00.',
            ]);
        }

        /*
         * Perubahan jadwal mendapat waktu check-in
         * selama 30 menit setelah personel ditambahkan.
         */
        $checkinDeadline = $isScheduleChange
            ? $now->copy()->addMinutes(30)
            : $normalDeadline;

        $existingMember = WfhScheduleMember::query()
            ->where('schedule_id', $wfhSchedule->id)
            ->where('user_id', $person->id)
            ->first();

        /*
         * Personel yang masih terdaftar tidak boleh
         * dimasukkan dua kali.
         */
        if (
            $existingMember
            && $existingMember->member_status !== 'cancelled'
        ) {
            throw ValidationException::withMessages([
                'user_id' =>
                    'Personel tersebut sudah terdaftar dalam jadwal.',
            ]);
        }

        $member = DB::transaction(function () use (
            $request,
            $wfhSchedule,
            $person,
            $existingMember,
            $isScheduleChange,
            $validated,
            $now,
            $checkinDeadline
        ) {
            $memberData = [
                'schedule_id' => $wfhSchedule->id,
                'user_id' => $person->id,

                'member_status' => $isScheduleChange
                    ? 'schedule_change'
                    : 'scheduled',

                'added_by' => $request->user()->id,
                'is_schedule_change' => $isScheduleChange,

                'change_reason' => $isScheduleChange
                    ? trim($validated['change_reason'])
                    : null,

                'added_at' => $now,
                'checkin_deadline' => $checkinDeadline,
                'cancelled_at' => null,
            ];

            /*
             * Jika sebelumnya pernah dibatalkan, data lama
             * diaktifkan kembali agar tidak melanggar unique key.
             */
            if ($existingMember) {
                $existingMember->update($memberData);
                $member = $existingMember;
            } else {
                $member = WfhScheduleMember::create($memberData);
            }

            /*
             * Membuat notifikasi dalam website untuk Personel.
             */
            AppNotification::create([
                'user_id' => $person->id,
                'type' => 'wfh_schedule',
                'title' => 'Jadwal WFH',
                'message' => $isScheduleChange
                    ? 'Anda ditambahkan ke jadwal WFH karena perubahan jadwal. Batas check-in: '
                        . $checkinDeadline->format('H:i')
                        . ' WIB.'
                    : 'Anda dijadwalkan mengikuti WFH pada '
                        . $wfhSchedule->wfh_date
                            ->translatedFormat('d F Y')
                        . '.',

                'related_type' => WfhSchedule::class,
                'related_id' => $wfhSchedule->id,
                'is_read' => false,
                'read_at' => null,
            ]);

            return $member;
        });

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $isScheduleChange
                ? 'schedule_change_member_added'
                : 'wfh_schedule_member_added',

            'description' => 'Admin menambahkan '
                . $person->name
                . ' ke jadwal WFH tanggal '
                . $wfhSchedule->wfh_date->format('d-m-Y')
                . '.',

            'subject_type' => WfhScheduleMember::class,
            'subject_id' => $member->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with(
            'success',
            $isScheduleChange
                ? 'Personel berhasil ditambahkan sebagai perubahan jadwal. Batas check-in 30 menit.'
                : 'Personel berhasil ditambahkan ke jadwal.'
        );
    }

    /**
     * Membatalkan keikutsertaan seorang personel.
     *
     * Data tidak dihapus permanen agar riwayat perubahan
     * jadwal tetap tersimpan.
     */
    public function cancel(
        Request $request,
        WfhSchedule $wfhSchedule,
        WfhScheduleMember $wfhScheduleMember
    ): RedirectResponse {
        /*
         * Memastikan anggota benar-benar berasal
         * dari jadwal pada URL.
         */
        if (
            $wfhScheduleMember->schedule_id
            !== $wfhSchedule->id
        ) {
            abort(404);
        }

        if (
            ! in_array(
                $wfhSchedule->status,
                ['draft', 'active'],
                true
            )
        ) {
            return back()->with(
                'error',
                'Anggota tidak dapat dibatalkan dari jadwal tersebut.'
            );
        }

        if ($wfhScheduleMember->member_status === 'cancelled') {
            return back()->with(
                'error',
                'Keikutsertaan personel sudah dibatalkan.'
            );
        }

        /*
         * Personel tidak dapat dibatalkan setelah check-in.
         */
        $hasCheckedIn = $wfhScheduleMember
            ->attendance()
            ->whereNotNull('checkin_at')
            ->exists();

        if ($hasCheckedIn) {
            return back()->with(
                'error',
                'Personel tidak dapat dibatalkan karena sudah melakukan check-in.'
            );
        }

        $person = $wfhScheduleMember->user;

        DB::transaction(function () use (
            $wfhSchedule,
            $wfhScheduleMember,
            $person
        ) {
            $wfhScheduleMember->update([
                'member_status' => 'cancelled',
                'cancelled_at' => now('Asia/Jakarta'),
            ]);

            /*
             * Setelah satu anggota dibatalkan, jadwal tidak lagi
             * dianggap mencakup seluruh personel.
             */
            if ($wfhSchedule->is_all_personnel) {
                $wfhSchedule->update([
                    'is_all_personnel' => false,
                ]);
            }

            AppNotification::create([
                'user_id' => $person->id,
                'type' => 'wfh_schedule_cancelled',
                'title' => 'Keikutsertaan WFH Dibatalkan',
                'message' =>
                    'Keikutsertaan Anda pada jadwal WFH tanggal '
                    . $wfhSchedule->wfh_date
                        ->translatedFormat('d F Y')
                    . ' telah dibatalkan oleh Admin.',

                'related_type' => WfhSchedule::class,
                'related_id' => $wfhSchedule->id,
                'is_read' => false,
                'read_at' => null,
            ]);
        });

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'wfh_schedule_member_cancelled',
            'description' => 'Admin membatalkan keikutsertaan '
                . $person->name
                . ' pada jadwal WFH tanggal '
                . $wfhSchedule->wfh_date->format('d-m-Y')
                . '.',

            'subject_type' => WfhScheduleMember::class,
            'subject_id' => $wfhScheduleMember->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with(
            'success',
            'Keikutsertaan personel berhasil dibatalkan.'
        );
    }
}
