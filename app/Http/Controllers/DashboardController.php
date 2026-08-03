<?php

namespace App\Http\Controllers;

use App\Models\WfhScheduleMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Mengarahkan pengguna ke dashboard sesuai role.
     */
    public function index(
        Request $request
    ): RedirectResponse {
        $user = $request->user()->loadMissing('role');

        return match ($user->role?->name) {
            'Admin' => redirect()->route('admin.dashboard'),

            'Pimpinan' => redirect()->route('leader.dashboard'),

            'Personel' => redirect()->route(
                'personnel.dashboard'
            ),

            default => abort(
                403,
                'Role akun tidak dikenali oleh sistem.'
            ),
        };
    }

    /**
     * Dashboard Admin.
     */
    public function admin(Request $request): View
    {
        $user = $request->user()->load(['role', 'unit']);

        return view('dashboards.admin', compact('user'));
    }

    /**
     * Dashboard Pimpinan.
     */
    public function leader(Request $request): View
    {
        $user = $request->user()->load(['role', 'unit']);

        return view('dashboards.leader', compact('user'));
    }

    /**
     * Dashboard Personel.
     */
    public function personnel(Request $request): View
    {
        $user = $request->user()->load(['role', 'unit']);

        $testMode = app()->environment('local')
            && filter_var(
                config('sikerja.attendance_test_mode'),
                FILTER_VALIDATE_BOOLEAN
            );

        $membershipQuery = WfhScheduleMember::query()
            ->with([
                'schedule',
                'attendance',
                'workReport',
            ])
            ->where('user_id', $user->id)
            ->whereIn('member_status', [
                'scheduled',
                'schedule_change',
                'present',
            ])
            ->whereHas('schedule', function ($query) {
                $query->where('status', 'active');
            });

        if ($testMode) {
            $membership = $membershipQuery
                ->get()
                ->sortByDesc(function ($member) {
                    return $member
                        ->schedule
                        ?->wfh_date
                        ?->timestamp ?? 0;
                })
                ->first();
        } else {
            $membership = $membershipQuery
                ->whereHas('schedule', function ($query) {
                    $query->whereDate(
                        'wfh_date',
                        now('Asia/Jakarta')->toDateString()
                    );
                })
                ->first();
        }

        $unreadNotifications = $user
            ->appNotifications()
            ->unread()
            ->count();

        /*
         * Menghitung rencana kerja pribadi yang telah dibuat.
         */
        $personalPlanCount = $membership?->workReport
            ?->items()
            ->where('source_type', 'personal_plan')
            ->count() ?? 0;

        return view('dashboards.personnel', compact(
            'user',
            'membership',
            'unreadNotifications',
            'personalPlanCount',
            'testMode'
        ));
    }
}
