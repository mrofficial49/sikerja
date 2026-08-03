<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Unit;
use App\Models\WorkItem;
use App\Models\WorkItemFile;
use App\Models\WorkReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WorkReportReviewController extends Controller
{
    /**
     * Menampilkan daftar laporan WFH.
     *
     * Halaman ini digunakan oleh Admin dan Pimpinan
     * untuk melihat laporan yang menunggu pemeriksaan.
     */
    public function index(Request $request): View
    {
        /*
         * Mengambil filter dari URL.
         */
        $status = $request->input('status');
        $date = $request->input('date');
        $unitId = $request->input('unit_id');
        $search = trim(
            (string) $request->input('search')
        );

        /*
         * Status laporan yang boleh ditampilkan
         * pada halaman verifikasi.
         */
        $allowedStatuses = [
            'waiting_verification',
            'needs_revision',
            'approved',
        ];

        /*
         * Abaikan filter status yang tidak dikenal.
         */
        if (
            $status
            && ! in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            $status = null;
        }

        $reports = WorkReport::query()
            ->with([
                'scheduleMember.user.unit',
                'scheduleMember.schedule',
                'scheduleMember.attendance',
            ])
            ->withCount('items')

            /*
             * Laporan draft belum ditampilkan karena
             * belum dikirim oleh Personel.
             */
            ->whereIn(
                'status',
                $allowedStatuses
            )

            /*
             * Filter berdasarkan status laporan.
             */
            ->when(
                $status,
                function ($query) use ($status) {
                    $query->where(
                        'status',
                        $status
                    );
                }
            )

            /*
             * Filter berdasarkan tanggal WFH.
             */
            ->when(
                $date,
                function ($query) use ($date) {
                    $query->whereHas(
                        'scheduleMember.schedule',
                        function ($scheduleQuery) use ($date) {
                            $scheduleQuery->whereDate(
                                'wfh_date',
                                $date
                            );
                        }
                    );
                }
            )

            /*
             * Filter berdasarkan unit kerja.
             */
            ->when(
                $unitId,
                function ($query) use ($unitId) {
                    $query->whereHas(
                        'scheduleMember.user',
                        function ($userQuery) use ($unitId) {
                            $userQuery->where(
                                'unit_id',
                                $unitId
                            );
                        }
                    );
                }
            )

            /*
             * Pencarian berdasarkan nama, ID login,
             * pangkat, atau jabatan Personel.
             */
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->whereHas(
                        'scheduleMember.user',
                        function ($userQuery) use ($search) {
                            $userQuery->where(
                                function ($subQuery) use ($search) {
                                    $subQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'login_id',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'rank',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'position',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                        }
                    );
                }
            )

            /*
             * Laporan menunggu verifikasi ditampilkan
             * paling atas.
             */
            ->orderByRaw(
                "CASE
                    WHEN status = 'waiting_verification' THEN 1
                    WHEN status = 'needs_revision' THEN 2
                    WHEN status = 'approved' THEN 3
                    ELSE 4
                END"
            )
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        /*
         * Mengambil daftar unit untuk filter.
         */
        $units = Unit::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
         * Menentukan nama route berdasarkan role
         * Admin atau Pimpinan.
         */
        $reviewRoutePrefix = $this->routePrefix(
            $request
        );

        return view(
            'reviews.work-reports.index',
            compact(
                'reports',
                'units',
                'status',
                'date',
                'unitId',
                'search',
                'reviewRoutePrefix'
            )
        );
    }

    /**
     * Menampilkan detail satu laporan WFH.
     */
    public function show(
        Request $request,
        WorkReport $workReport
    ): View {
        /*
         * Memuat identitas Personel, presensi,
         * pekerjaan, dan bukti PDF.
         */
        $workReport->load([
            'scheduleMember.user.unit',
            'scheduleMember.schedule',
            'scheduleMember.attendance',

            'items' => function ($query) {
                $query->orderBy('created_at');
            },

            'items.files' => function ($query) {
                $query
                    ->where('is_available', true)
                    ->orderByDesc('uploaded_at');
            },
        ]);

        /*
         * Laporan draft belum dikirim, sehingga
         * tidak boleh diperiksa.
         */
        if ($workReport->status === 'draft') {
            abort(
                404,
                'Laporan belum dikirim oleh Personel.'
            );
        }

        $reviewRoutePrefix = $this->routePrefix(
            $request
        );

        return view(
            'reviews.work-reports.show',
            compact(
                'workReport',
                'reviewRoutePrefix'
            )
        );
    }

    /**
     * Menyetujui laporan kerja Personel.
     */
    public function approve(
        Request $request,
        WorkReport $workReport
    ): RedirectResponse {
        /*
         * Catatan persetujuan tidak wajib.
         */
        $validated = $request->validate([
            'verification_note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'verification_note.max' =>
                'Catatan verifikasi maksimal 2.000 karakter.',
        ]);

        $workReport->load([
            'scheduleMember.user',
            'scheduleMember.schedule',
            'scheduleMember.attendance',
        ]);

        /*
         * Hanya laporan menunggu verifikasi
         * yang dapat disetujui.
         */
        if (
            $workReport->status
            !== 'waiting_verification'
        ) {
            return back()->with(
                'error',
                'Laporan ini tidak sedang menunggu verifikasi.'
            );
        }

        /*
         * Personel harus sudah check-out sebelum
         * laporannya disetujui.
         */
        if (
            ! $workReport
                ->scheduleMember
                ?->attendance
                ?->checkout_at
        ) {
            return back()->with(
                'error',
                'Laporan belum dapat disetujui karena Personel belum check-out.'
            );
        }

        DB::transaction(function () use (
            $request,
            $workReport,
            $validated
        ) {
            /*
             * lockForUpdate mencegah laporan diproses
             * oleh dua reviewer secara bersamaan.
             */
            $lockedReport = WorkReport::query()
                ->lockForUpdate()
                ->findOrFail($workReport->id);

            if (
                $lockedReport->status
                !== 'waiting_verification'
            ) {
                return;
            }

            $lockedReport->forceFill([
                'status' => 'approved',
                'is_locked' => true,

                'verified_by' =>
                    $request->user()->id,

                'verified_at' =>
                    now('Asia/Jakarta'),

                'verification_note' => filled(
                    $validated['verification_note'] ?? null
                )
                    ? trim(
                        $validated['verification_note']
                    )
                    : null,
            ])->save();

            $person = $workReport
    ->scheduleMember
    ?->user;

if ($person) {
    AppNotification::create([
        'user_id' => $person->id,
        'type' => 'work_report_approved',
        'title' => 'Laporan WFH Disetujui',

        'message' =>
            'Laporan WFH Anda telah diperiksa dan disetujui.',

        'related_type' => WorkReport::class,
        'related_id' => $lockedReport->id,
        'is_read' => false,
        'read_at' => null,
    ]);
}

            /*
             * Menyimpan aktivitas persetujuan
             * ke activity_logs.
             */
            ActivityLog::create([
                'user_id' =>
                    $request->user()->id,

                'action' =>
                    'work_report_approved',

                'description' =>
                    $request->user()->name
                    . ' menyetujui laporan WFH milik '
                    . (
                        $workReport
                            ->scheduleMember
                            ?->user
                            ?->name
                        ?? 'Personel'
                    )
                    . '.',

                'subject_type' =>
                    WorkReport::class,

                'subject_id' =>
                    $lockedReport->id,

                'ip_address' =>
                    $request->ip(),

                'user_agent' =>
                    $request->userAgent(),
            ]);
        });

        return back()->with(
            'success',
            'Laporan kerja berhasil disetujui.'
        );
    }

    /**
     * Mengembalikan laporan kepada Personel
     * untuk dilakukan perbaikan.
     */
    public function requestRevision(
        Request $request,
        WorkReport $workReport
    ): RedirectResponse {
        /*
         * Catatan revisi wajib diisi agar Personel
         * mengetahui bagian yang harus diperbaiki.
         */
        $validated = $request->validate([
            'verification_note' => [
                'required',
                'string',
                'max:2000',
            ],
        ], [
            'verification_note.required' =>
                'Catatan revisi wajib diisi.',

            'verification_note.max' =>
                'Catatan revisi maksimal 2.000 karakter.',
        ]);

        $workReport->load([
            'scheduleMember.user',
        ]);

        if (
            $workReport->status
            !== 'waiting_verification'
        ) {
            return back()->with(
                'error',
                'Laporan ini tidak sedang menunggu verifikasi.'
            );
        }

        DB::transaction(function () use (
            $request,
            $workReport,
            $validated
        ) {
            $lockedReport = WorkReport::query()
                ->lockForUpdate()
                ->findOrFail($workReport->id);

            if (
                $lockedReport->status
                !== 'waiting_verification'
            ) {
                return;
            }

            /*
             * is_locked dibuat false supaya laporan
             * dapat diperbaiki oleh Personel.
             */
            $lockedReport->forceFill([
                'status' => 'needs_revision',
                'is_locked' => false,

                'verified_by' =>
                    $request->user()->id,

                'verified_at' =>
                    now('Asia/Jakarta'),

                'verification_note' =>
                    trim(
                        $validated['verification_note']
                    ),
            ])->save();

            $person = $workReport
    ->scheduleMember
    ?->user;

if ($person) {
    AppNotification::create([
        'user_id' => $person->id,
        'type' => 'work_report_revision',
        'title' => 'Laporan WFH Perlu Revisi',

        'message' =>
            'Laporan WFH perlu diperbaiki. Catatan: '
            . trim(
                $validated['verification_note']
            ),

        'related_type' => WorkReport::class,
        'related_id' => $lockedReport->id,
        'is_read' => false,
        'read_at' => null,
    ]);
}

            ActivityLog::create([
                'user_id' =>
                    $request->user()->id,

                'action' =>
                    'work_report_revision_requested',

                'description' =>
                    $request->user()->name
                    . ' meminta revisi laporan WFH milik '
                    . (
                        $workReport
                            ->scheduleMember
                            ?->user
                            ?->name
                        ?? 'Personel'
                    )
                    . '.',

                'subject_type' =>
                    WorkReport::class,

                'subject_id' =>
                    $lockedReport->id,

                'ip_address' =>
                    $request->ip(),

                'user_agent' =>
                    $request->userAgent(),
            ]);
        });

        return back()->with(
            'success',
            'Laporan dikembalikan kepada Personel untuk diperbaiki.'
        );
    }

    /**
     * Mengunduh bukti PDF pekerjaan.
     */
    public function downloadFile(
        WorkReport $workReport,
        WorkItemFile $workItemFile
    ) {
        /*
         * Memastikan file benar-benar berasal dari
         * pekerjaan pada laporan yang sedang dibuka.
         *
         * Kolom relasi work_item_files menggunakan
         * item_id, bukan work_item_id.
         */
        $belongsToReport = WorkItem::query()
            ->where(
                'report_id',
                $workReport->id
            )
            ->whereKey(
                (int) $workItemFile->item_id
            )
            ->exists();

        if (! $belongsToReport) {
            abort(
                404,
                'Bukti pekerjaan tidak ditemukan.'
            );
        }

        /*
         * File yang sudah ditandai tidak tersedia
         * tidak boleh diunduh.
         */
        if (! $workItemFile->is_available) {
            return back()->with(
                'error',
                'Bukti pekerjaan sudah tidak tersedia.'
            );
        }

        /*
         * Memastikan file fisik masih tersedia
         * pada storage privat Laravel.
         */
        if (
            blank($workItemFile->file_path)
            || ! Storage::disk('local')->exists(
                $workItemFile->file_path
            )
        ) {
            return back()->with(
                'error',
                'File bukti tidak ditemukan pada penyimpanan.'
            );
        }

        /*
         * basename digunakan untuk membersihkan
         * nama file ketika diunduh.
         */
        $downloadName = basename(
            $workItemFile->original_name
                ?: 'bukti-pekerjaan.pdf'
        );

        return Storage::disk('local')->download(
            $workItemFile->file_path,
            $downloadName,
            [
                'Content-Type' =>
                    'application/pdf',

                'X-Content-Type-Options' =>
                    'nosniff',

                'Cache-Control' =>
                    'private, no-store',
            ]
        );
    }

    /**
     * Menentukan awalan route berdasarkan role.
     */
    private function routePrefix(
        Request $request
    ): string {
        $roleName = $request
            ->user()
            ->role
            ?->name;

        return $roleName === 'Admin'
            ? 'admin.reports'
            : 'leader.reports';
    }
}
