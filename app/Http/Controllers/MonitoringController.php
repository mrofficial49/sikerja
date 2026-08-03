<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\WfhSchedule;
use App\Models\WfhScheduleMember;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Menampilkan monitoring pelaksanaan WFH.
     *
     * Halaman ini dapat digunakan oleh Admin dan Pimpinan.
     */
    public function index(Request $request): View
    {
        /*
         * Mengambil nilai filter.
         */
        $scheduleId = $request->integer('schedule_id');
        $unitId = $request->integer('unit_id');
        $search = trim(
            (string) $request->input('search')
        );

        /*
         * Mengambil seluruh jadwal untuk pilihan filter.
         */
        $schedules = WfhSchedule::query()
            ->orderByDesc('wfh_date')
            ->get();

        /*
         * Menentukan jadwal yang sedang dipantau.
         *
         * Urutannya:
         * 1. Jadwal yang dipilih melalui filter.
         * 2. Jadwal berstatus aktif.
         * 3. Jadwal terbaru.
         */
        $selectedSchedule = null;

        if ($scheduleId) {
            $selectedSchedule = $schedules->firstWhere(
                'id',
                $scheduleId
            );
        }

        if (! $selectedSchedule) {
            $selectedSchedule = $schedules->firstWhere(
                'status',
                'active'
            );
        }

        if (! $selectedSchedule) {
            $selectedSchedule = $schedules->first();
        }

        /*
         * Membuat query dasar daftar Personel.
         */
        $membersQuery = WfhScheduleMember::query()
            ->with([
                'user.unit',
                'schedule',
                'attendance',

                /*
                 * Menghitung jumlah pekerjaan pada laporan.
                 */
                'workReport' => function ($query) {
                    $query->withCount('items');
                },
            ])

            /*
             * Personel yang dibatalkan tidak dimasukkan.
             */
            ->whereNull('cancelled_at')

            /*
             * Jika belum ada jadwal, jangan tampilkan data.
             */
            ->when(
                $selectedSchedule,
                function ($query) use ($selectedSchedule) {
                    $query->where(
                        'schedule_id',
                        $selectedSchedule->id
                    );
                },
                function ($query) {
                    $query->whereRaw('1 = 0');
                }
            )

            /*
             * Filter berdasarkan unit kerja.
             */
            ->when(
                $unitId,
                function ($query) use ($unitId) {
                    $query->whereHas(
                        'user',
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
             * Pencarian identitas Personel.
             */
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->whereHas(
                        'user',
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
            ->orderBy('user_id');

        /*
         * Mengambil seluruh data untuk perhitungan statistik.
         */
        $summaryMembers = (
            clone $membersQuery
        )->get();

        /*
         * Mengambil data tabel menggunakan pagination.
         */
        $members = (
            clone $membersQuery
        )
            ->paginate(25)
            ->withQueryString();

        /*
         * Menghitung statistik presensi.
         */
        $summary = [
            'total' => $summaryMembers->count(),

            'checked_in' => $summaryMembers
                ->filter(
                    fn ($member) =>
                        filled(
                            $member->attendance?->checkin_at
                        )
                )
                ->count(),

            'not_checked_in' => $summaryMembers
                ->filter(
                    fn ($member) =>
                        blank(
                            $member->attendance?->checkin_at
                        )
                )
                ->count(),

            'checked_out' => $summaryMembers
                ->filter(
                    fn ($member) =>
                        filled(
                            $member->attendance?->checkout_at
                        )
                )
                ->count(),

            'not_checked_out' => $summaryMembers
                ->filter(
                    fn ($member) =>
                        blank(
                            $member->attendance?->checkout_at
                        )
                )
                ->count(),

            /*
             * Menghitung status laporan.
             */
            'waiting_verification' => $summaryMembers
                ->filter(
                    fn ($member) =>
                        $member->workReport?->status
                        === 'waiting_verification'
                )
                ->count(),

            'needs_revision' => $summaryMembers
                ->filter(
                    fn ($member) =>
                        $member->workReport?->status
                        === 'needs_revision'
                )
                ->count(),

            'approved' => $summaryMembers
                ->filter(
                    fn ($member) =>
                        $member->workReport?->status
                        === 'approved'
                )
                ->count(),

            'not_submitted' => $summaryMembers
                ->filter(
                    fn ($member) =>
                        ! $member->workReport
                        || in_array(
                            $member->workReport->status,
                            [
                                'draft',
                                'incomplete',
                            ],
                            true
                        )
                )
                ->count(),
        ];

        /*
         * Mengambil daftar unit untuk filter.
         */
        $units = Unit::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
         * Menentukan route berdasarkan role pengguna.
         */
        $routePrefix = $request
            ->user()
            ->role
            ?->name === 'Admin'
                ? 'admin.monitoring'
                : 'leader.monitoring';

        /*
         * Route detail laporan yang sudah dibuat sebelumnya.
         */
        $reportRoutePrefix = $request
            ->user()
            ->role
            ?->name === 'Admin'
                ? 'admin.reports'
                : 'leader.reports';

        return view(
            'monitoring.index',
            compact(
                'members',
                'summary',
                'units',
                'schedules',
                'selectedSchedule',
                'unitId',
                'search',
                'routePrefix',
                'reportRoutePrefix'
            )
        );
    }
}
