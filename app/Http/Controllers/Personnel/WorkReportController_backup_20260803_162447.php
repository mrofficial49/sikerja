<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\User;
use App\Models\WorkItem;
use App\Models\WorkReport;
use App\Models\WfhScheduleMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkReportController extends Controller
{
    /**
     * Menampilkan ringkasan laporan kerja Personel.
     */
    public function show(
        Request $request
    ): View|RedirectResponse {
        /*
         * Cari jadwal WFH aktif milik pengguna
         * yang sedang login.
         */
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
         * Laporan hanya bisa dibuka setelah check-in.
         */
        if (! $membership->attendance?->checkin_at) {
            return redirect()
                ->route('personnel.attendance.show')
                ->with(
                    'error',
                    'Silakan melakukan check-in terlebih dahulu.'
                );
        }

        /*
         * Ambil laporan yang sudah ada atau buat
         * laporan draft sebagai pengaman.
         */
        $report = WorkReport::firstOrCreate(
            [
                'schedule_member_id' => $membership->id,
            ],
            [
                'status' => 'draft',
                'is_locked' => false,
            ]
        );

        /*
         * Mengambil seluruh pekerjaan beserta file PDF
         * yang masih tersedia.
         */
        $items = WorkItem::query()
            ->with([
                'files' => function ($query) {
                    $query->where('is_available', true);
                },
            ])
            ->where('report_id', $report->id)
            ->orderBy('created_at')
            ->get();

        $personalPlanCount = $items
            ->where('source_type', 'personal_plan')
            ->count();

        $completedCount = $items
            ->where('status', 'completed')
            ->count();

        $checkoutCompleted = filled(
            $membership->attendance?->checkout_at
        );

        return view('personnel.reports.show', compact(
            'membership',
            'report',
            'items',
            'personalPlanCount',
            'completedCount',
            'checkoutCompleted'
        ));
    }

    /**
     * Mengirim laporan untuk diverifikasi
     * oleh Admin atau Pimpinan.
     */
    public function submit(
        Request $request
    ): RedirectResponse {
        $membership = $this->findCurrentMembership(
            $request->user()->id
        );

        if (! $membership) {
            return redirect()
                ->route('personnel.dashboard')
                ->with(
                    'error',
                    'Tidak ada jadwal WFH aktif.'
                );
        }

        if (! $membership->attendance?->checkin_at) {
            return redirect()
                ->route('personnel.attendance.show')
                ->with(
                    'error',
                    'Silakan melakukan check-in terlebih dahulu.'
                );
        }

        /*
         * Laporan tidak dapat dikirim kembali
         * setelah Personel melakukan check-out.
         */
        /*
         * Mengambil laporan milik Personel.
         *
         * Variabel $report wajib dibuat sebelum
         * status laporan diperiksa.
         */
        $report = WorkReport::query()
            ->where(
                'schedule_member_id',
                $membership->id
            )
            ->first();

        /*
         * Pengamanan apabila laporan tidak ditemukan.
         */
        if (! $report) {
            return back()->with(
                'error',
                'Laporan kerja tidak ditemukan.'
            );
        }

        $checkoutCompleted = filled(
            $membership->attendance?->checkout_at
        );

        /*
         * Setelah check-out, laporan hanya boleh
         * dikirim ulang apabila reviewer meminta revisi.
         */
        if (
            $checkoutCompleted
            && ! in_array(
                $report->status,
                [
                    'needs_revision',
                    'draft',
                ],
                true
            )
        ) {
            return back()->with(
                'error',
                'Laporan setelah check-out hanya dapat dikirim ulang ketika sedang direvisi.'
            );
        }

        $report = WorkReport::query()
            ->where(
                'schedule_member_id',
                $membership->id
            )
            ->first();

        if (! $report) {
            throw ValidationException::withMessages([
                'report' => 'Laporan kerja belum tersedia.',
            ]);
        }

        if ($report->is_locked) {
            return back()->with(
                'error',
                'Laporan sudah dikunci.'
            );
        }

        $items = WorkItem::query()
            ->where('report_id', $report->id)
            ->orderBy('created_at')
            ->get();

        /*
         * Personel wajib mempunyai minimal
         * satu rencana kerja pribadi.
         */
        if (
            $items
                ->where('source_type', 'personal_plan')
                ->isEmpty()
        ) {
            throw ValidationException::withMessages([
                'report' =>
                    'Minimal satu rencana kerja pribadi wajib dibuat.',
            ]);
        }

        /*
         * Periksa kelengkapan setiap pekerjaan.
         */
        foreach ($items as $item) {
            $this->validateWorkItem($item);
        }

        DB::transaction(function () use (
            $request,
            $membership,
            $report
        ) {
            /*
             * Lock baris mencegah laporan dikirim
             * dua kali secara bersamaan.
             */
            $lockedReport = WorkReport::query()
                ->lockForUpdate()
                ->findOrFail($report->id);

            if ($lockedReport->is_locked) {
                throw ValidationException::withMessages([
                    'report' => 'Laporan sudah dikunci.',
                ]);
            }

            $lockedReport->forceFill([
                'status' => 'waiting_verification',
                /*
                 * Jika Personel sudah check-out,
                 * laporan dikunci kembali setelah
                 * dikirim ulang.
                 */
                'is_locked' => filled(
                    $membership->attendance?->checkout_at
                ),

                'submitted_at' => now('Asia/Jakarta'),
                'verified_by' => null,
                'verified_at' => null,
            ])->save();

            /*
             * Cari seluruh Admin dan Pimpinan aktif
             * sebagai penerima notifikasi.
             */
            $reviewers = User::query()
                ->where('is_active', true)
                ->whereHas('role', function ($query) {
                    $query->whereIn('name', [
                        'Admin',
                        'Pimpinan',
                    ]);
                })
                ->get();

            foreach ($reviewers as $reviewer) {
                AppNotification::create([
                    'user_id' => $reviewer->id,
                    'type' => 'work_report_submitted',
                    'title' => 'Laporan WFH Dikirim',

                    'message' =>
                        $request->user()->name
                        . ' telah mengirim laporan WFH tanggal '
                        . $membership
                            ->schedule
                            ->wfh_date
                            ->translatedFormat('d F Y')
                        . '.',

                    'related_type' => WorkReport::class,
                    'related_id' => $lockedReport->id,
                    'is_read' => false,
                    'read_at' => null,
                ]);
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'work_report_submitted',
                'description' =>
                    'Personel mengirim laporan kerja WFH.',
                'subject_type' => WorkReport::class,
                'subject_id' => $lockedReport->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()
            ->route('personnel.report.show')
            ->with(
                'success',
                'Laporan berhasil dikirim. Silakan melakukan check-out.'
            );
    }

    /**
     * Memeriksa kelengkapan satu pekerjaan.
     */
    private function validateWorkItem(
        WorkItem $item
    ): void {
        /*
         * Pekerjaan tidak boleh masih belum dimulai.
         */
        if ($item->status === 'not_started') {
            throw ValidationException::withMessages([
                'report' =>
                    'Pekerjaan "'
                    . $item->title
                    . '" masih berstatus Belum Dimulai.',
            ]);
        }

        /*
         * Pekerjaan yang sudah berjalan wajib
         * mempunyai uraian progres.
         */
        if (
            in_array(
                $item->status,
                [
                    'in_progress',
                    'blocked',
                    'completed',
                ],
                true
            )
            && blank($item->progress)
        ) {
            throw ValidationException::withMessages([
                'report' =>
                    'Progres pekerjaan "'
                    . $item->title
                    . '" belum diisi.',
            ]);
        }

        /*
         * Pekerjaan terkendala wajib memiliki
         * penjelasan kendala.
         */
        if (
            $item->status === 'blocked'
            && blank($item->obstacle)
        ) {
            throw ValidationException::withMessages([
                'report' =>
                    'Kendala pekerjaan "'
                    . $item->title
                    . '" belum dijelaskan.',
            ]);
        }

        /*
         * Pekerjaan yang belum selesai wajib memiliki
         * rencana tindak lanjut.
         */
        if (
            ! in_array(
                $item->status,
                ['completed', 'cancelled'],
                true
            )
            && blank($item->follow_up_plan)
        ) {
            throw ValidationException::withMessages([
                'report' =>
                    'Rencana tindak lanjut pekerjaan "'
                    . $item->title
                    . '" wajib diisi.',
            ]);
        }
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

        /*
         * Dalam mode normal, hanya jadwal hari ini
         * yang dapat digunakan.
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
         * Dalam mode pengujian, gunakan jadwal aktif
         * terbaru agar fitur dapat diuji kapan saja.
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
     * Mode pengujian hanya aktif di environment local.
     */
    private function isTestMode(): bool
    {
        return app()->environment('local')
            && filter_var(
                config('sikerja.attendance_test_mode'),
                FILTER_VALIDATE_BOOLEAN
            );
    }
}
