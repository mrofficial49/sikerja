<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\WorkItem;
use App\Models\WorkReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Menampilkan seluruh notifikasi milik pengguna
     * yang sedang login.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');

        /*
         * Hanya status berikut yang diperbolehkan.
         */
        if (
            $status
            && ! in_array(
                $status,
                ['unread', 'read'],
                true
            )
        ) {
            $status = null;
        }

        $notifications = AppNotification::query()
            /*
             * Pengguna hanya boleh melihat
             * notifikasi miliknya sendiri.
             */
            ->where(
                'user_id',
                $request->user()->id
            )

            /*
             * Filter notifikasi belum dibaca.
             */
            ->when(
                $status === 'unread',
                function ($query) {
                    $query->where(
                        'is_read',
                        false
                    );
                }
            )

            /*
             * Filter notifikasi sudah dibaca.
             */
            ->when(
                $status === 'read',
                function ($query) {
                    $query->where(
                        'is_read',
                        true
                    );
                }
            )

            /*
             * Notifikasi terbaru ditampilkan
             * paling atas.
             */
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $unreadCount = AppNotification::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->where('is_read', false)
            ->count();

        return view(
            'notifications.index',
            compact(
                'notifications',
                'unreadCount',
                'status'
            )
        );
    }

    /**
     * Membuka notifikasi dan mengarahkan pengguna
     * ke halaman yang berkaitan.
     */
    public function open(
        Request $request,
        AppNotification $notification
    ): RedirectResponse {
        /*
         * Mencegah pengguna membuka notifikasi
         * milik pengguna lain.
         */
        if (
            (int) $notification->user_id
            !== (int) $request->user()->id
        ) {
            abort(403);
        }

        /*
         * Tandai sebagai sudah dibaca.
         */
        $notification->markAsRead();

        $roleName = $request
            ->user()
            ->role
            ?->name;

        /*
         * Notifikasi terkait laporan kerja.
         */
        if (
            $notification->related_type
            === WorkReport::class
            && $notification->related_id
        ) {
            $report = WorkReport::find(
                $notification->related_id
            );

            if ($report) {
                if ($roleName === 'Admin') {
                    return redirect()->route(
                        'admin.reports.show',
                        $report
                    );
                }

                if ($roleName === 'Pimpinan') {
                    return redirect()->route(
                        'leader.reports.show',
                        $report
                    );
                }

                return redirect()->route(
                    'personnel.report.show'
                );
            }
        }

        /*
         * Notifikasi terkait tugas Pimpinan.
         */
        if (
            $notification->related_type
            === WorkItem::class
            && $notification->related_id
        ) {
            $workItem = WorkItem::find(
                $notification->related_id
            );

            if ($workItem) {
                if ($roleName === 'Pimpinan') {
                    return redirect()->route(
                        'leader.tasks.index'
                    );
                }

                if ($roleName === 'Personel') {
                    return redirect()->route(
                        'personnel.work-items.index'
                    );
                }
            }
        }

        /*
         * Jika tujuan tidak dikenali,
         * kembali ke dashboard.
         */
        return redirect()->route('dashboard');
    }

    /**
     * Menandai seluruh notifikasi sebagai dibaca.
     */
    public function markAllAsRead(
        Request $request
    ): RedirectResponse {
        AppNotification::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now('Asia/Jakarta'),
            ]);

        return back()->with(
            'success',
            'Semua notifikasi telah ditandai sebagai dibaca.'
        );
    }
}
