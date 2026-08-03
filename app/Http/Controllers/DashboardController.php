<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use App\Models\WfhSchedule;
use App\Models\WfhScheduleMember;
use App\Models\WorkItem;
use App\Models\WorkReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Mengarahkan pengguna menuju dashboard sesuai role.
     */
    public function index(
        Request $request
    ): RedirectResponse {
        $user = $request
            ->user()
            ->loadMissing('role');

        return match ($user->role?->name) {
            'Admin' => redirect()
                ->route('admin.dashboard'),

            'Pimpinan' => redirect()
                ->route('leader.dashboard'),

            'Personel' => redirect()
                ->route('personnel.dashboard'),

            default => abort(
                403,
                'Role akun tidak dikenali oleh sistem.'
            ),
        };
    }

    /**
     * Menampilkan dashboard Admin.
     */
    public function admin(
        Request $request
    ): View {
        $user = $request
            ->user()
            ->load(['role', 'unit']);

        /*
         * Jadwal WFH aktif yang terbaru.
         */
        $activeSchedule = WfhSchedule::query()
            ->where('status', 'active')
            ->orderByDesc('wfh_date')
            ->first();

        /*
         * Query anggota pada jadwal aktif.
         */
        $activeMembersQuery = WfhScheduleMember::query()
            ->when(
                $activeSchedule,
                function ($query) use ($activeSchedule) {
                    $query->where(
                        'schedule_id',
                        $activeSchedule->id
                    );
                },
                function ($query) {
                    /*
                     * Jika tidak ada jadwal aktif,
                     * query dibuat tidak menghasilkan data.
                     */
                    $query->whereRaw('1 = 0');
                }
            )
            ->whereNull('cancelled_at');

        /*
         * Statistik utama Admin.
         */
        $statistics = [
            'users' => User::query()->count(),

            'units' => Unit::query()->count(),

            'active_schedules' => WfhSchedule::query()
                ->where('status', 'active')
                ->count(),

            'scheduled_personnel' => (
                clone $activeMembersQuery
            )->count(),

            'checked_in' => (
                clone $activeMembersQuery
            )
                ->whereHas(
                    'attendance',
                    function ($query) {
                        $query->whereNotNull('checkin_at');
                    }
                )
                ->count(),

            'checked_out' => (
                clone $activeMembersQuery
            )
                ->whereHas(
                    'attendance',
                    function ($query) {
                        $query->whereNotNull('checkout_at');
                    }
                )
                ->count(),

            'waiting_reports' => WorkReport::query()
                ->where(
                    'status',
                    'waiting_verification'
                )
                ->count(),

            'revision_reports' => WorkReport::query()
                ->where(
                    'status',
                    'needs_revision'
                )
                ->count(),

            'approved_reports' => WorkReport::query()
                ->where(
                    'status',
                    'approved'
                )
                ->count(),

            'unread_notifications' => $user
                ->appNotifications()
                ->unread()
                ->count(),
        ];

        return view(
            'dashboards.admin',
            compact(
                'user',
                'activeSchedule',
                'statistics'
            )
        );
    }

    /**
     * Menampilkan dashboard Pimpinan.
     */
    public function leader(
        Request $request
    ): View {
        $user = $request
            ->user()
            ->load(['role', 'unit']);

        $activeSchedule = WfhSchedule::query()
            ->where('status', 'active')
            ->orderByDesc('wfh_date')
            ->first();

        $activeMembersQuery = WfhScheduleMember::query()
            ->when(
                $activeSchedule,
                function ($query) use ($activeSchedule) {
                    $query->where(
                        'schedule_id',
                        $activeSchedule->id
                    );
                },
                function ($query) {
                    $query->whereRaw('1 = 0');
                }
            )
            ->whereNull('cancelled_at');

        /*
         * Statistik dashboard Pimpinan.
         */
        $statistics = [
            'scheduled_personnel' => (
                clone $activeMembersQuery
            )->count(),

            'checked_in' => (
                clone $activeMembersQuery
            )
                ->whereHas(
                    'attendance',
                    function ($query) {
                        $query->whereNotNull('checkin_at');
                    }
                )
                ->count(),

            'checked_out' => (
                clone $activeMembersQuery
            )
                ->whereHas(
                    'attendance',
                    function ($query) {
                        $query->whereNotNull('checkout_at');
                    }
                )
                ->count(),

            /*
             * Seluruh tugas yang dibuat oleh
             * Pimpinan yang sedang login.
             */
            'leader_tasks' => WorkItem::query()
                ->where(
                    'source_type',
                    'leader_task'
                )
                ->where(
                    'created_by',
                    $user->id
                )
                ->count(),

            'active_tasks' => WorkItem::query()
                ->where(
                    'source_type',
                    'leader_task'
                )
                ->where(
                    'created_by',
                    $user->id
                )
                ->whereNotIn(
                    'status',
                    [
                        'completed',
                        'cancelled',
                    ]
                )
                ->count(),

            'completed_tasks' => WorkItem::query()
                ->where(
                    'source_type',
                    'leader_task'
                )
                ->where(
                    'created_by',
                    $user->id
                )
                ->where(
                    'status',
                    'completed'
                )
                ->count(),

            'waiting_reports' => WorkReport::query()
                ->where(
                    'status',
                    'waiting_verification'
                )
                ->count(),

            'revision_reports' => WorkReport::query()
                ->where(
                    'status',
                    'needs_revision'
                )
                ->count(),

            'approved_reports' => WorkReport::query()
                ->where(
                    'status',
                    'approved'
                )
                ->count(),

            'unread_notifications' => $user
                ->appNotifications()
                ->unread()
                ->count(),
        ];

        return view(
            'dashboards.leader',
            compact(
                'user',
                'activeSchedule',
                'statistics'
            )
        );
    }

    /**
     * Menampilkan dashboard Personel.
     */
    public function personnel(
        Request $request
    ): View {
        $user = $request
            ->user()
            ->load(['role', 'unit']);

        /*
         * Mode pengujian hanya digunakan pada local.
         */
        $testMode = app()->environment('local')
            && filter_var(
                config('sikerja.attendance_test_mode'),
                FILTER_VALIDATE_BOOLEAN
            );

        $membershipQuery = WfhScheduleMember::query()
            ->with([
                'schedule',
                'attendance',
                'workReport.items',
            ])
            ->where('user_id', $user->id)
            ->whereIn(
                'member_status',
                [
                    'scheduled',
                    'schedule_change',
                    'present',
                ]
            )
            ->whereHas(
                'schedule',
                function ($query) {
                    $query->where(
                        'status',
                        'active'
                    );
                }
            );

        /*
         * Pada mode pengujian, gunakan jadwal aktif terbaru.
         */
        if ($testMode) {
            $membership = $membershipQuery
                ->get()
                ->sortByDesc(
                    function ($member) {
                        return $member
                            ->schedule
                            ?->wfh_date
                            ?->timestamp ?? 0;
                    }
                )
                ->first();
        } else {
            /*
             * Pada production, jadwal harus sesuai
             * dengan tanggal hari ini.
             */
            $membership = $membershipQuery
                ->whereHas(
                    'schedule',
                    function ($query) {
                        $query->whereDate(
                            'wfh_date',
                            now('Asia/Jakarta')
                                ->toDateString()
                        );
                    }
                )
                ->first();
        }

        $unreadNotifications = $user
            ->appNotifications()
            ->unread()
            ->count();

        /*
         * Mengambil koleksi pekerjaan dari laporan.
         */
        $workItems = $membership
            ?->workReport
            ?->items
            ?? collect();

        /*
         * Statistik pekerjaan Personel.
         */
        $workStatistics = [
            'total' => $workItems
                ->where('status', '!=', 'cancelled')
                ->count(),

            'personal_plans' => $workItems
                ->where(
                    'source_type',
                    'personal_plan'
                )
                ->where('status', '!=', 'cancelled')
                ->count(),

            'leader_tasks' => $workItems
                ->where(
                    'source_type',
                    'leader_task'
                )
                ->where('status', '!=', 'cancelled')
                ->count(),

            'completed' => $workItems
                ->where(
                    'status',
                    'completed'
                )
                ->count(),

            'in_progress' => $workItems
                ->whereIn(
                    'status',
                    [
                        'not_started',
                        'in_progress',
                        'blocked',
                    ]
                )
                ->count(),
        ];

        return view(
            'dashboards.personnel',
            compact(
                'user',
                'membership',
                'unreadNotifications',
                'workStatistics',
                'testMode'
            )
        );
    }
}
