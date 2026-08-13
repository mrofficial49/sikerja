<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\WorkItem;
use App\Models\WorkReport;
use App\Models\WfhScheduleMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkItemController extends Controller
{
    /**
     * Menampilkan daftar rencana kerja pribadi.
     */
    public function index(Request $request): View|RedirectResponse
    {
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

        if (! $membership->attendance?->checkin_at) {
            return redirect()
                ->route('personnel.attendance.show')
                ->with(
                    'error',
                    'Silakan melakukan check-in terlebih dahulu.'
                );
        }

        /*
         * Laporan kerja biasanya sudah dibuat otomatis
         * ketika Personel berhasil check-in.
         *
         * firstOrCreate digunakan sebagai pengaman apabila
         * laporan belum terbentuk.
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

        $personalPlans = $report->items()
            ->where('source_type', 'personal_plan')
            ->orderBy('created_at')
            ->get();

        $leaderTasks = $report->items()
            ->where('source_type', 'leader_task')
            ->orderBy('created_at')
            ->get();

        $requiresChangeReason = $report->status !== 'draft';

        $canModify = $this->reportCanBeModified(
            $membership,
            $report
        );

        return view('personnel.work-items.index', compact(
            'membership',
            'report',
            'personalPlans',
            'leaderTasks',
            'requiresChangeReason',
            'canModify'
        ));
    }

    /**
     * Menampilkan formulir tambah rencana kerja.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $report = $this->resolveEditableReport($request);

        if ($report instanceof RedirectResponse) {
            return $report;
        }

        return view('personnel.work-items.create', [
            'report' => $report,
            'requiresChangeReason' =>
                $report->status !== 'draft',
        ]);
    }

    /**
     * Menyimpan rencana kerja pribadi.
     */
    public function store(Request $request): RedirectResponse
    {
        $report = $this->resolveEditableReport($request);

        if ($report instanceof RedirectResponse) {
            return $report;
        }

        $validated = $request->validate(
            $this->validationRules($report),
            $this->validationMessages()
        );

        $workItem = DB::transaction(function () use (
            $request,
            $report,
            $validated
        ) {
            /*
             * Jika laporan sudah pernah dikirim atau disetujui,
             * perubahan akan mengembalikannya menjadi draft.
             */
            $this->resetReportAfterChange(
                $report,
                $validated['change_reason'] ?? null
            );

            $workItem = WorkItem::create([
                'report_id' => $report->id,
                'created_by' => $request->user()->id,
                'source_type' => 'personal_plan',
                'title' => trim($validated['title']),
                'description' => trim($validated['description']),
                'target_result' => trim(
                    $validated['target_result']
                ),
                'status' => 'not_started',
                'progress' => null,
                'obstacle' => null,
                'follow_up_plan' => null,
                'continue_offline' => false,
                'cancelled_by' => null,
                'cancelled_at' => null,
                'assigned_at' => null,
            ]);

            $this->writeLog(
                $request,
                'personal_work_plan_created',
                'Personel menambahkan rencana kerja: '
                    . $workItem->title
                    . '.',
                $workItem
            );

            return $workItem;
        });

        return redirect()
            ->route('personnel.work-items.index')
            ->with(
                'success',
                'Rencana kerja berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan formulir edit rencana kerja.
     */
    public function edit(
        Request $request,
        WorkItem $workItem
    ): View|RedirectResponse {
        $report = $this->resolveEditableReport($request);

        if ($report instanceof RedirectResponse) {
            return $report;
        }

        $this->ensureOwnedPersonalPlan(
    $report,
    $workItem
);

/*
 * Rincian pekerjaan hanya boleh diedit
 * selama pekerjaan belum selesai atau dibatalkan.
 */
if (! $this->workItemCanBeEdited($workItem)) {
    return redirect()
        ->route('personnel.work-items.index')
        ->with(
            'error',
            'Rincian pekerjaan yang sudah selesai atau dibatalkan tidak dapat diedit.'
        );
}

        return view('personnel.work-items.edit', [
            'report' => $report,
            'workItem' => $workItem,
            'requiresChangeReason' =>
                $report->status !== 'draft',
        ]);
    }

    /**
     * Memperbarui rencana kerja pribadi.
     */
    public function update(
        Request $request,
        WorkItem $workItem
    ): RedirectResponse {
        $report = $this->resolveEditableReport($request);

        if ($report instanceof RedirectResponse) {
            return $report;
        }

        $this->ensureOwnedPersonalPlan(
            $report,
            $workItem
        );

/*
 * Pemeriksaan ini diulang pada proses update.
 *
 * Kenapa?
 * Karena pengguna bisa saja mencoba mengirim request
 * update secara langsung tanpa membuka form edit.
 */
if (! $this->workItemCanBeEdited($workItem)) {
    return redirect()
        ->route('personnel.work-items.index')
        ->with(
            'error',
            'Rincian pekerjaan yang sudah selesai atau dibatalkan tidak dapat diedit.'
        );
}

        $validated = $request->validate(
            $this->validationRules($report),
            $this->validationMessages()
        );

        DB::transaction(function () use (
            $request,
            $report,
            $workItem,
            $validated
        ) {
            $this->resetReportAfterChange(
                $report,
                $validated['change_reason'] ?? null
            );

            $workItem->update([
                'title' => trim($validated['title']),
                'description' => trim($validated['description']),
                'target_result' => trim(
                    $validated['target_result']
                ),
            ]);

            $this->writeLog(
                $request,
                'personal_work_plan_updated',
                'Personel memperbarui rencana kerja: '
                    . $workItem->title
                    . '.',
                $workItem
            );
        });

        return redirect()
            ->route('personnel.work-items.index')
            ->with(
                'success',
                'Rencana kerja berhasil diperbarui.'
            );
    }

    /**
     * Menghapus rencana kerja pribadi.
     */
    public function destroy(
        Request $request,
        WorkItem $workItem
    ): RedirectResponse {
        $report = $this->resolveEditableReport($request);

        if ($report instanceof RedirectResponse) {
            return $report;
        }

        $this->ensureOwnedPersonalPlan(
            $report,
            $workItem
        );

        /*
         * Personel wajib mempunyai minimal satu
         * rencana kerja pribadi.
         */
        $personalPlanCount = $report->items()
            ->where('source_type', 'personal_plan')
            ->count();

        if ($personalPlanCount <= 1) {
            return back()->with(
                'error',
                'Rencana kerja terakhir tidak dapat dihapus. Personel wajib memiliki minimal satu rencana kerja.'
            );
        }

        $rules = [
            'change_reason' => [
                $report->status !== 'draft'
                    ? 'required'
                    : 'nullable',
                'string',
                'max:1000',
            ],
        ];

        $validated = $request->validate($rules, [
            'change_reason.required' =>
                'Alasan perubahan wajib diisi karena laporan sudah pernah dikirim.',
            'change_reason.max' =>
                'Alasan perubahan maksimal 1.000 karakter.',
        ]);

        $title = $workItem->title;

        DB::transaction(function () use (
            $request,
            $report,
            $workItem,
            $validated,
            $title
        ) {
            $this->resetReportAfterChange(
                $report,
                $validated['change_reason'] ?? null
            );

            $workItemId = $workItem->id;

            /*
             * Penghapusan ini hanya diperbolehkan sebelum
             * check-out dan sebelum laporan dikunci.
             */
            $workItem->delete();

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'personal_work_plan_deleted',
                'description' =>
                    'Personel menghapus rencana kerja: '
                    . $title
                    . '.',
                'subject_type' => WorkItem::class,
                'subject_id' => $workItemId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return back()->with(
            'success',
            'Rencana kerja berhasil dihapus.'
        );
    }

    /**
     * Aturan validasi formulir rencana kerja.
     */
    private function validationRules(
        WorkReport $report
    ): array {
        return [
            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'description' => [
                'required',
                'string',
                'max:5000',
            ],

            'target_result' => [
                'required',
                'string',
                'max:5000',
            ],

            /*
             * Alasan perubahan wajib jika laporan tidak lagi
             * berstatus draft.
             */
            'change_reason' => [
                $report->status !== 'draft'
                    ? 'required'
                    : 'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Pesan validasi Bahasa Indonesia.
     */
    private function validationMessages(): array
    {
        return [
            'title.required' =>
                'Judul rencana kerja wajib diisi.',

            'title.max' =>
                'Judul rencana kerja maksimal 200 karakter.',

            'description.required' =>
                'Uraian pekerjaan wajib diisi.',

            'description.max' =>
                'Uraian pekerjaan maksimal 5.000 karakter.',

            'target_result.required' =>
                'Target hasil wajib diisi.',

            'target_result.max' =>
                'Target hasil maksimal 5.000 karakter.',

            'change_reason.required' =>
                'Alasan perubahan wajib diisi karena laporan sudah pernah dikirim.',

            'change_reason.max' =>
                'Alasan perubahan maksimal 1.000 karakter.',
        ];
    }

    /**
     * Mengambil laporan aktif dan memastikan laporan
     * masih boleh diubah.
     */
    private function resolveEditableReport(
        Request $request
    ): WorkReport|RedirectResponse {
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

        if (! $membership->attendance?->checkin_at) {
            return redirect()
                ->route('personnel.attendance.show')
                ->with(
                    'error',
                    'Silakan melakukan check-in terlebih dahulu.'
                );
        }

        $report = WorkReport::firstOrCreate(
            [
                'schedule_member_id' => $membership->id,
            ],
            [
                'status' => 'draft',
                'is_locked' => false,
            ]
        );

        if (
            ! $this->reportCanBeModified(
                $membership,
                $report
            )
        ) {
            return redirect()
                ->route('personnel.work-items.index')
                ->with(
                    'error',
                    'Rencana kerja tidak dapat diubah setelah check-out, kecuali laporan sedang direvisi.'
                );
        }

        return $report;
    }

    /**
     * Memastikan rencana kerja merupakan milik
     * pengguna yang sedang login.
     */
    private function ensureOwnedPersonalPlan(
        WorkReport $report,
        WorkItem $workItem
    ): void {
        if (
            $workItem->report_id !== $report->id
            || $workItem->source_type !== 'personal_plan'
        ) {
            abort(
                403,
                'Anda tidak memiliki akses ke rencana kerja tersebut.'
            );
        }
    }

    /**
 * Menentukan apakah rincian pekerjaan masih boleh diedit.
 *
 * Pekerjaan masih dapat diperbaiki selama status:
 * - not_started
 * - in_progress
 * - blocked
 *
 * Pekerjaan dikunci apabila:
 * - completed
 * - cancelled
 */
private function workItemCanBeEdited(
    WorkItem $workItem
): bool {
    return ! in_array(
        $workItem->status,
        [
            'completed',
            'cancelled',
        ],
        true
    );
}

    /**
     * Jika laporan pernah dikirim atau disetujui,
     * perubahan mengembalikan laporan menjadi draft.
     */
    private function resetReportAfterChange(
        WorkReport $report,
        ?string $changeReason
    ): void {
        if ($report->status === 'draft') {
            return;
        }

        $report->update([
            'status' => 'draft',
            'submitted_at' => null,
            'last_change_reason' =>
                trim((string) $changeReason),
            'last_changed_at' => now('Asia/Jakarta'),
            'verified_by' => null,
            'verified_at' => null,
            'completed_offline_at' => null,
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
     * Mode pengujian hanya boleh aktif di local.
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
     * Menulis aktivitas ke activity_logs.
     */
    private function writeLog(
        Request $request,
        string $action,
        string $description,
        WorkItem $workItem
    ): void {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => WorkItem::class,
            'subject_id' => $workItem->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Menentukan apakah laporan masih boleh diperbaiki.
     *
     * Laporan yang sudah check-out hanya boleh diubah
     * ketika reviewer meminta revisi.
     */
    private function reportCanBeModified(
        WfhScheduleMember $membership,
        WorkReport $report
    ): bool {
        /*
         * Laporan yang benar-benar terkunci
         * tidak boleh diubah.
         */
        if ($report->is_locked) {
            return false;
        }

        /*
         * Sebelum check-out, laporan masih boleh diubah.
         */
        if (! $membership->attendance?->checkout_at) {
            return true;
        }

        /*
         * Setelah check-out, perubahan hanya diizinkan
         * apabila status laporan adalah needs_revision.
         */
        /*
         * needs_revision adalah status awal saat laporan
         * dikembalikan oleh reviewer.
         *
         * draft adalah status setelah Personel mulai
         * melakukan perbaikan.
         */
        return in_array(
            $report->status,
            [
                'needs_revision',
                'draft',
            ],
            true
        );
    }

}
