<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\WorkItem;
use App\Models\WorkReport;
use App\Models\WfhScheduleMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderTaskController extends Controller
{
    /**
     * Menampilkan daftar tugas yang pernah diberikan Pimpinan.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $search = trim((string) $request->input('search'));

        $tasks = WorkItem::query()
            ->with([
                'creator',
                'report.scheduleMember.user.unit',
                'report.scheduleMember.schedule',
            ])
            ->where('source_type', 'leader_task')
            ->where('created_by', $request->user()->id)

            /*
             * Filter status pekerjaan.
             */
            ->when(
                $status,
                function ($query) use ($status) {
                    $query->where('status', $status);
                }
            )

            /*
             * Pencarian nama Personel atau judul tugas.
             */
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where(
                                'title',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'report.scheduleMember.user',
                                function ($userQuery) use ($search) {
                                    $userQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'login_id',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                    });
                }
            )
            ->orderByDesc('assigned_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'leader.tasks.index',
            compact(
                'tasks',
                'status',
                'search'
            )
        );
    }

    /**
     * Menampilkan form pemberian tugas.
     */
    public function create(): View
    {
        /*
         * Mengambil Personel yang terdaftar pada
         * jadwal WFH aktif.
         */
        $members = WfhScheduleMember::query()
            ->with([
                'user.unit',
                'schedule',
                'attendance',
                'workReport',
            ])
            ->whereHas('schedule', function ($query) {
                $query->where('status', 'active');
            })
            ->whereHas('user')
            ->get()
            ->sortBy(function ($member) {
                return strtolower(
                    $member->user?->name ?? ''
                );
            })
            ->values();

        return view(
            'leader.tasks.create',
            compact('members')
        );
    }

    /**
     * Menyimpan tugas baru dari Pimpinan.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'schedule_member_id' => [
                'required',
                'integer',
                'exists:wfh_schedule_members,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
                'max:5000',
            ],

            'target_result' => [
                'required',
                'string',
                'max:3000',
            ],
        ], [
            'schedule_member_id.required' =>
                'Personel penerima tugas wajib dipilih.',

            'schedule_member_id.exists' =>
                'Data Personel tidak ditemukan.',

            'title.required' =>
                'Judul tugas wajib diisi.',

            'description.required' =>
                'Uraian tugas wajib diisi.',

            'target_result.required' =>
                'Target hasil wajib diisi.',
        ]);

        $member = WfhScheduleMember::query()
            ->with([
                'user',
                'schedule',
                'attendance',
                'workReport',
            ])
            ->findOrFail(
                $validated['schedule_member_id']
            );

        /*
         * Tugas hanya boleh diberikan pada jadwal aktif.
         */
        if ($member->schedule?->status !== 'active') {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Tugas hanya dapat diberikan pada jadwal WFH aktif.'
                );
        }

        /*
         * Tidak boleh menambahkan tugas setelah
         * Personel selesai check-out.
         */
        if ($member->attendance?->checkout_at) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Tugas tidak dapat diberikan karena Personel sudah check-out.'
                );
        }

        DB::transaction(function () use (
            $request,
            $validated,
            $member
        ) {
            /*
             * Membuat laporan apabila Personel belum
             * memiliki laporan untuk jadwal tersebut.
             */
            $report = WorkReport::query()
                ->firstOrCreate(
                    [
                        'schedule_member_id' =>
                            $member->id,
                    ],
                    [
                        'status' => 'draft',
                        'is_locked' => false,
                    ]
                );

            /*
             * Laporan yang terkunci tidak boleh
             * menerima tugas baru.
             */
            if ($report->is_locked) {
                abort(
                    422,
                    'Laporan Personel sudah dikunci.'
                );
            }

            $task = WorkItem::create([
                'report_id' => $report->id,

                /*
                 * created_by menyimpan ID Pimpinan
                 * yang memberikan tugas.
                 */
                'created_by' => $request->user()->id,

                'source_type' => 'leader_task',
                'title' => trim($validated['title']),
                'description' => trim(
                    $validated['description']
                ),
                'target_result' => trim(
                    $validated['target_result']
                ),

                /*
                 * Saat pertama diberikan, pekerjaan
                 * belum dimulai oleh Personel.
                 */
                'status' => 'not_started',
                'progress' => null,
                'obstacle' => null,
                'follow_up_plan' => null,
                'continue_offline' => false,
                'assigned_at' => now('Asia/Jakarta'),
            ]);
            /*
 * =====================================================
 * NOTIFIKASI TUGAS BARU
 * =====================================================
 *
 * Setelah tugas berhasil dibuat, sistem mengirimkan
 * notifikasi kepada Personel penerima tugas.
 */
AppNotification::create([
    /*
     * Notifikasi dikirim kepada user yang terdaftar
     * sebagai anggota jadwal WFH.
     */
    'user_id' => $member->user_id,

    /*
     * Jenis notifikasi digunakan untuk menentukan
     * warna dan tujuan notifikasi.
     */
    'type' => 'leader_task',

    /*
     * Judul singkat yang tampil pada pusat notifikasi.
     */
    'title' => 'Tugas Baru dari Pimpinan',

    /*
     * Isi pesan notifikasi.
     */
    'message' =>
        'Anda menerima tugas baru dari '
        . $request->user()->name
        . ': '
        . $task->title
        . '.',

    /*
     * Notifikasi dihubungkan dengan tugas yang
     * baru dibuat.
     */
    'related_type' => WorkItem::class,
    'related_id' => $task->id,

    /*
     * Notifikasi pertama kali berstatus belum dibaca.
     */
    'is_read' => false,
    'read_at' => null,
]);

/*
 * Laporan tetap dibuka agar tugas dapat
 * dikerjakan oleh Personel.
 */
$report->forceFill([
    'status' => 'draft',
    'is_locked' => false,
])->save();

            /*
             * Laporan tetap dibuka agar tugas dapat
             * dikerjakan oleh Personel.
             */
            $report->forceFill([
                'status' => 'draft',
                'is_locked' => false,
            ])->save();

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'leader_task_created',

                'description' =>
                    $request->user()->name
                    . ' memberikan tugas "'
                    . $task->title
                    . '" kepada '
                    . ($member->user?->name ?? 'Personel')
                    . '.',

                'subject_type' => WorkItem::class,
                'subject_id' => $task->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()
            ->route('leader.tasks.index')
            ->with(
                'success',
                'Tugas berhasil diberikan kepada Personel.'
            );
    }

    /**
     * Membatalkan tugas dari Pimpinan.
     */
    public function cancel(
        Request $request,
        WorkItem $workItem
    ): RedirectResponse {
        $workItem->load([
            'report.scheduleMember.attendance',
            'report.scheduleMember.user',
        ]);

        /*
         * Pimpinan hanya boleh membatalkan
         * tugas yang dibuatnya sendiri.
         */
        if (
            $workItem->source_type !== 'leader_task'
            || (int) $workItem->created_by
                !== (int) $request->user()->id
        ) {
            abort(403);
        }

        if (
            $workItem->report?->is_locked
            || $workItem
                ->report
                ?->scheduleMember
                ?->attendance
                ?->checkout_at
        ) {
            return back()->with(
                'error',
                'Tugas tidak dapat dibatalkan karena laporan sudah dikunci atau Personel sudah check-out.'
            );
        }

        if ($workItem->status === 'cancelled') {
            return back()->with(
                'error',
                'Tugas ini sudah dibatalkan.'
            );
        }

        DB::transaction(function () use (
            $request,
            $workItem
        ) {
            $workItem->forceFill([
                'status' => 'cancelled',
                'cancelled_by' =>
                    $request->user()->id,
                'cancelled_at' =>
                    now('Asia/Jakarta'),
            ])->save();

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'leader_task_cancelled',

                'description' =>
                    $request->user()->name
                    . ' membatalkan tugas "'
                    . $workItem->title
                    . '".',

                'subject_type' => WorkItem::class,
                'subject_id' => $workItem->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return back()->with(
            'success',
            'Tugas berhasil dibatalkan.'
        );
    }
}
