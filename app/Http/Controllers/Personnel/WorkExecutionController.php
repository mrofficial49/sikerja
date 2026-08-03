<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Models\WorkItem;
use App\Models\WorkItemFile;
use App\Models\WorkReport;
use App\Models\WfhScheduleMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class WorkExecutionController extends Controller
{
    /**
     * Menampilkan halaman pelaksanaan satu pekerjaan.
     */
    public function edit(
        Request $request,
        WorkItem $workItem
    ): View|RedirectResponse {
        $context = $this->resolveContext(
            $request,
            $workItem
        );

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        [$membership, $report] = $context;

        /*
         * Mengambil file bukti yang masih tersedia.
         */
        $files = WorkItemFile::query()
            ->where('item_id', $workItem->id)
            ->where('is_available', true)
            ->orderByDesc('uploaded_at')
            ->get();

        /*
         * Data tidak dapat diubah setelah check-out
         * atau ketika laporan telah dikunci.
         */
        $canModify = $this->reportCanBeModified(
            $membership,
            $report
        );

        $requiresChangeReason = $report->status !== 'draft';

        return view(
            'personnel.work-execution.edit',
            compact(
                'membership',
                'report',
                'workItem',
                'files',
                'canModify',
                'requiresChangeReason'
            )
        );
    }

    /**
     * Menyimpan status dan hasil pelaksanaan pekerjaan.
     */
    public function update(
        Request $request,
        WorkItem $workItem
    ): RedirectResponse {
        $context = $this->resolveContext(
            $request,
            $workItem
        );

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        [$membership, $report] = $context;

        if (
            ! $this->reportCanBeModified(
                $membership,
                $report
            )
        ) {
            return back()->with(
                'error',
                'Pelaksanaan pekerjaan tidak dapat diubah setelah check-out.'
            );
        }

        /*
         * Checkbox HTML tidak mengirim nilai ketika
         * tidak dicentang.
         */
        $request->merge([
            'continue_offline' => $request->boolean(
                'continue_offline'
            ),
        ]);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'not_started',
                    'in_progress',
                    'blocked',
                    'completed',
                    'cancelled',
                ]),
            ],

            'progress' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'obstacle' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'follow_up_plan' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'continue_offline' => [
                'required',
                'boolean',
            ],

            'change_reason' => [
                $report->status !== 'draft'
                    ? 'required'
                    : 'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'status.required' =>
                'Status pekerjaan wajib dipilih.',

            'status.in' =>
                'Status pekerjaan tidak valid.',

            'progress.max' =>
                'Uraian progres maksimal 5.000 karakter.',

            'obstacle.max' =>
                'Uraian kendala maksimal 5.000 karakter.',

            'follow_up_plan.max' =>
                'Rencana tindak lanjut maksimal 5.000 karakter.',

            'change_reason.required' =>
                'Alasan perubahan wajib diisi karena laporan sudah pernah dikirim.',

            'change_reason.max' =>
                'Alasan perubahan maksimal 1.000 karakter.',
        ]);

        /*
         * Progres wajib diisi apabila pekerjaan sudah
         * mulai dilaksanakan.
         */
        if (
            in_array(
                $validated['status'],
                ['in_progress', 'blocked', 'completed'],
                true
            )
            && blank($validated['progress'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'progress' =>
                    'Uraian progres wajib diisi untuk status tersebut.',
            ]);
        }

        /*
         * Kendala wajib dijelaskan jika status pekerjaan
         * adalah terkendala.
         */
        if (
            $validated['status'] === 'blocked'
            && blank($validated['obstacle'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'obstacle' =>
                    'Kendala wajib dijelaskan jika pekerjaan berstatus terkendala.',
            ]);
        }

        /*
         * Jika pekerjaan akan diteruskan secara offline,
         * rencana tindak lanjut wajib diisi.
         */
        if (
            $validated['continue_offline']
            && blank($validated['follow_up_plan'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'follow_up_plan' =>
                    'Rencana tindak lanjut wajib diisi jika pekerjaan dilanjutkan secara offline.',
            ]);
        }

        /*
         * Pekerjaan selesai atau dibatalkan tidak perlu
         * ditandai dilanjutkan secara offline.
         */
        $continueOffline = in_array(
            $validated['status'],
            ['completed', 'cancelled'],
            true
        )
            ? false
            : (bool) $validated['continue_offline'];

        DB::transaction(function () use (
            $request,
            $report,
            $workItem,
            $validated,
            $continueOffline
        ) {
            $this->resetReportAfterChange(
                $report,
                $validated['change_reason'] ?? null
            );

            /*
             * forceFill digunakan agar tidak bergantung
             * pada pengaturan fillable model.
             */
            $workItem->forceFill([
                'status' => $validated['status'],

                'progress' => filled(
                    $validated['progress'] ?? null
                )
                    ? trim($validated['progress'])
                    : null,

                'obstacle' => filled(
                    $validated['obstacle'] ?? null
                )
                    ? trim($validated['obstacle'])
                    : null,

                'follow_up_plan' => filled(
                    $validated['follow_up_plan'] ?? null
                )
                    ? trim($validated['follow_up_plan'])
                    : null,

                'continue_offline' => $continueOffline,
            ])->save();

            $this->writeLog(
                $request,
                'work_execution_updated',
                'Personel memperbarui pelaksanaan pekerjaan: '
                    . $workItem->title
                    . '.',
                WorkItem::class,
                $workItem->id
            );
        });

        return back()->with(
            'success',
            'Pelaksanaan pekerjaan berhasil diperbarui.'
        );
    }

    /**
     * Mengunggah bukti pekerjaan dalam format PDF.
     */
    public function uploadFile(
        Request $request,
        WorkItem $workItem
    ): RedirectResponse {
        $context = $this->resolveContext(
            $request,
            $workItem
        );

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        [$membership, $report] = $context;

        if (
            ! $this->reportCanBeModified(
                $membership,
                $report
            )
        ) {
            return back()->with(
                'error',
                'Bukti pekerjaan tidak dapat ditambahkan setelah check-out.'
            );
        }

        $validated = $request->validate([
            /*
             * Maksimal file adalah 10 MB.
             * 10 MB = 10.240 KB.
             */
            'file' => [
                'required',
                'file',
                'mimetypes:application/pdf',
                'extensions:pdf',
                'max:10240',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'change_reason' => [
                $report->status !== 'draft'
                    ? 'required'
                    : 'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'file.required' =>
                'File bukti wajib dipilih.',

            'file.file' =>
                'Bukti yang diunggah tidak valid.',

            'file.mimetypes' =>
                'Bukti pekerjaan harus berupa PDF.',

            'file.extensions' =>
                'Ekstensi file harus .pdf.',

            'file.max' =>
                'Ukuran PDF maksimal 10 MB.',

            'description.max' =>
                'Keterangan file maksimal 1.000 karakter.',

            'change_reason.required' =>
                'Alasan perubahan wajib diisi karena laporan sudah pernah dikirim.',
        ]);

        $uploadedFile = $request->file('file');

        /*
         * Nama file pada server dibuat acak agar tidak
         * bertabrakan dan tidak mudah ditebak.
         */
        $storedName = 'bukti_'
            . $workItem->id
            . '_'
            . Str::uuid()
            . '.pdf';

        $storedPath = null;

        try {
            $fileRecord = DB::transaction(function () use (
                $request,
                $report,
                $workItem,
                $validated,
                $uploadedFile,
                $storedName,
                &$storedPath
            ) {
                $this->resetReportAfterChange(
                    $report,
                    $validated['change_reason'] ?? null
                );

                /*
                 * File disimpan di disk local yang bersifat
                 * privat, bukan di public/storage.
                 */
                $storedPath = $uploadedFile->storeAs(
                    'work-items/'
                        . $report->id
                        . '/'
                        . $workItem->id,
                    $storedName,
                    'local'
                );

                if (! $storedPath) {
                    throw new \RuntimeException(
                        'File bukti gagal disimpan.'
                    );
                }

                $retentionDays = (int) SystemSetting::getValue(
                    'file_retention_days',
                    30
                );

                $fileRecord = new WorkItemFile();

                $fileRecord->forceFill([
                    'item_id' => $workItem->id,
                    'original_name' =>
                        $uploadedFile->getClientOriginalName(),
                    'stored_name' => $storedName,
                    'file_path' => $storedPath,

                    'description' => filled(
                        $validated['description'] ?? null
                    )
                        ? trim($validated['description'])
                        : null,

                    'file_size' => $uploadedFile->getSize(),
                    'mime_type' =>
                        $uploadedFile->getMimeType()
                        ?: 'application/pdf',

                    'uploaded_by' => $request->user()->id,
                    'uploaded_at' => now('Asia/Jakarta'),

                    'expires_at' => now('Asia/Jakarta')
                        ->addDays($retentionDays),

                    'deleted_at' => null,
                    'is_available' => true,
                ])->save();

                $this->writeLog(
                    $request,
                    'work_evidence_uploaded',
                    'Personel mengunggah bukti PDF untuk pekerjaan: '
                        . $workItem->title
                        . '.',
                    WorkItemFile::class,
                    $fileRecord->id
                );

                return $fileRecord;
            });
        } catch (Throwable $exception) {
            /*
             * Jika transaksi database gagal, file yang sudah
             * tersimpan dihapus agar tidak menjadi file yatim.
             */
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }

            throw $exception;
        }

        return back()->with(
            'success',
            'Bukti PDF berhasil diunggah.'
        );
    }

    /**
     * Mengunduh file bukti pekerjaan.
     */
    public function downloadFile(
        Request $request,
        WorkItem $workItem,
        WorkItemFile $workItemFile
    ) {
        /*
         * Memastikan pekerjaan dan file benar-benar
         * menjadi milik pengguna yang sedang login.
         */
        $context = $this->resolveContext(
            $request,
            $workItem
        );

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $this->ensureFileBelongsToItem(
            $workItem,
            $workItemFile
        );

        /*
         * File hanya dapat diunduh jika metadata masih aktif
         * dan file fisiknya masih tersedia pada disk local.
         */
        if (! $workItemFile->is_available) {
            return back()->with(
                'error',
                'File bukti sudah tidak tersedia.'
            );
        }

        $filePath = $workItemFile->file_path;

        if (
            blank($filePath)
            || ! Storage::disk('local')->exists($filePath)
        ) {
            return back()->with(
                'error',
                'File fisik tidak ditemukan pada penyimpanan.'
            );
        }

        /*
         * Membuka file sebagai stream sehingga file privat
         * tidak perlu dipindahkan ke folder public.
         */
        $stream = Storage::disk('local')->readStream(
            $filePath
        );

        if ($stream === false) {
            return back()->with(
                'error',
                'File tidak dapat dibaca dari penyimpanan.'
            );
        }

        /*
         * basename mencegah karakter path seperti garis miring
         * masuk ke nama file unduhan.
         */
        $originalName = basename(
            $workItemFile->original_name
                ?: $workItemFile->stored_name
                ?: 'bukti-pekerjaan.pdf'
        );

        /*
         * Nama file dibuat aman untuk header browser.
         */
        $downloadName = Str::ascii($originalName);

        $downloadName = preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            $downloadName
        );

        if (blank($downloadName)) {
            $downloadName = 'bukti-pekerjaan.pdf';
        }

        if (! str_ends_with(
            strtolower($downloadName),
            '.pdf'
        )) {
            $downloadName .= '.pdf';
        }

        /*
         * Mengirim isi file sedikit demi sedikit
         * sebagai attachment kepada browser.
         */
        return response()->streamDownload(
            function () use ($stream) {
                fpassthru($stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            $downloadName,
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' =>
                    'private, no-store, no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /**
     * Menghapus file bukti pekerjaan.
     */
    public function destroyFile(
        Request $request,
        WorkItem $workItem,
        WorkItemFile $workItemFile
    ): RedirectResponse {
        $context = $this->resolveContext(
            $request,
            $workItem
        );

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        [$membership, $report] = $context;

        $this->ensureFileBelongsToItem(
            $workItem,
            $workItemFile
        );

        if (
            ! $this->reportCanBeModified(
                $membership,
                $report
            )
        ) {
            return back()->with(
                'error',
                'Bukti pekerjaan tidak dapat dihapus setelah check-out.'
            );
        }

        if (! $workItemFile->is_available) {
            return back()->with(
                'error',
                'File bukti sudah tidak tersedia.'
            );
        }

        $validated = $request->validate([
            'change_reason' => [
                $report->status !== 'draft'
                    ? 'required'
                    : 'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'change_reason.required' =>
                'Alasan perubahan wajib diisi karena laporan sudah pernah dikirim.',

            'change_reason.max' =>
                'Alasan perubahan maksimal 1.000 karakter.',
        ]);

        DB::transaction(function () use (
            $request,
            $report,
            $workItem,
            $workItemFile,
            $validated
        ) {
            $this->resetReportAfterChange(
                $report,
                $validated['change_reason'] ?? null
            );

            /*
             * Hapus file fisik dari storage privat.
             */
            Storage::disk('local')->delete(
                $workItemFile->file_path
            );

            /*
             * Metadata tetap disimpan untuk riwayat audit.
             */
            $workItemFile->forceFill([
                'is_available' => false,
                'deleted_at' => now('Asia/Jakarta'),
            ])->save();

            $this->writeLog(
                $request,
                'work_evidence_deleted',
                'Personel menghapus bukti PDF dari pekerjaan: '
                    . $workItem->title
                    . '.',
                WorkItemFile::class,
                $workItemFile->id
            );
        });

        return back()->with(
            'success',
            'Bukti PDF berhasil dihapus.'
        );
    }

    /**
     * Mengambil jadwal, laporan, dan memastikan pekerjaan
     * merupakan milik Personel yang login.
     */
    private function resolveContext(
        Request $request,
        WorkItem $workItem
    ): array|RedirectResponse {
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

        /*
         * Personel tidak boleh membuka pekerjaan
         * milik laporan orang lain.
         */
        if ($workItem->report_id !== $report->id) {
            abort(
                403,
                'Anda tidak memiliki akses ke pekerjaan tersebut.'
            );
        }

        return [$membership, $report];
    }

   /**
 * Memastikan file berasal dari pekerjaan pada URL.
 */
private function ensureFileBelongsToItem(
    WorkItem $workItem,
    WorkItemFile $workItemFile
): void {
    /*
     * Keduanya diubah menjadi integer agar nilai "1"
     * dan 1 dianggap sebagai ID yang sama.
     */
    if (
        (int) $workItemFile->item_id
        !== (int) $workItem->id
    ) {
        abort(
            404,
            'File bukti tidak ditemukan pada pekerjaan ini.'
        );
    }
}

    /**
     * Mengembalikan laporan menjadi draft apabila laporan
     * sudah pernah dikirim atau diverifikasi.
     */
    private function resetReportAfterChange(
        WorkReport $report,
        ?string $changeReason
    ): void {
        if ($report->status === 'draft') {
            return;
        }

        $report->forceFill([
            'status' => 'draft',
            'submitted_at' => null,

            'last_change_reason' =>
                trim((string) $changeReason),

            'last_changed_at' => now('Asia/Jakarta'),
            'verified_by' => null,
            'verified_at' => null,
            'completed_offline_at' => null,
            'is_locked' => false,
        ])->save();
    }

    /**
     * Mencari keikutsertaan aktif milik Personel.
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
     * Mode pengujian hanya berlaku pada environment local.
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
        string $subjectType,
        int $subjectId
    ): void {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
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
