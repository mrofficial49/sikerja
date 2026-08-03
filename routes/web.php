<?php

use App\Http\Controllers\NotificationController;

use App\Http\Controllers\MonitoringController;

use App\Http\Controllers\Leader\LeaderTaskController;

use App\Http\Controllers\Review\WorkReportReviewController;

use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WfhScheduleController;
use App\Http\Controllers\Admin\WfhScheduleMemberController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Personnel\AttendanceController;
use App\Http\Controllers\Personnel\WorkItemController;
use App\Http\Controllers\Personnel\WorkExecutionController;
use App\Http\Controllers\Personnel\WorkReportController;
use App\Http\Controllers\Personnel\CheckoutController;
use App\Http\Middleware\EnsurePasswordIsChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Awal
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Route Pengguna yang Belum Login
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get(
        '/login',
        [LoginController::class, 'showLogin']
    )->name('login');

    Route::post(
        '/login',
        [LoginController::class, 'login']
    )->name('login.process');
});

/*
|--------------------------------------------------------------------------
| Route Pengguna yang Sudah Login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    /*
     * Logout tetap dapat dilakukan meskipun pengguna
     * belum mengganti password sementara.
     */
    Route::post(
        '/logout',
        [LoginController::class, 'logout']
    )->name('logout');

    Route::middleware('active')->group(function () {
        /*
         * Halaman perubahan password.
         */
        Route::get(
            '/password/change',
            [PasswordController::class, 'edit']
        )->name('password.change');

        Route::put(
            '/password/change',
            [PasswordController::class, 'update']
        )->name('password.update');

        /*
         * Pengguna wajib mengganti password sementara
         * sebelum membuka fitur utama.
         */
        Route::middleware(EnsurePasswordIsChanged::class)
            ->group(function () {
                /*
                 * Pengarah dashboard berdasarkan role.
                 */
                Route::get(
                    '/dashboard',
                    [DashboardController::class, 'index']
                )->name('dashboard');

                /*
                 * =====================================================
                 * AREA ADMIN
                 * =====================================================
                 */
                Route::prefix('admin')
                    ->name('admin.')
                    ->middleware('role:Admin')
                    ->group(function () {
                        Route::get(
                            '/dashboard',
                            [DashboardController::class, 'admin']
                        )->name('dashboard');

                        /*
                         * Route kelola unit:
                         * index, create, store, edit, dan update.
                         *
                         * Route show dan destroy tidak dibuat karena
                         * unit tidak dilihat lewat halaman detail dan
                         * tidak boleh dihapus permanen.
                         */
                        Route::resource(
                            'units',
                            UnitController::class
                        )->except([
                            'show',
                            'destroy',
                        ]);

                        /*
                         * Route untuk mengaktifkan atau
                         * menonaktifkan unit.
                         */
                        Route::patch(
                            '/units/{unit}/toggle-status',
                            [UnitController::class, 'toggleStatus']
                        )->name('units.toggle-status');


                        /*
                         * Kelola akun pengguna.
                         */
                        Route::resource(
                            'users',
                            UserController::class
                        )->except([
                            'show',
                            'destroy',
                        ]);

                        /*
                         * Aktifkan atau nonaktifkan akun.
                         */
                        Route::patch(
                            '/users/{user}/toggle-status',
                            [UserController::class, 'toggleStatus']
                        )->name('users.toggle-status');

                        /*
                         * Reset password sementara pengguna.
                         */
                        Route::patch(
                            '/users/{user}/reset-password',
                            [UserController::class, 'resetPassword']
                        )->name('users.reset-password');


                        /*
                         * Kelola jadwal WFH.
                         */
                        Route::resource(
                            'wfh-schedules',
                            WfhScheduleController::class
                        )->only([
                            'index',
                            'create',
                            'store',
                            'show',
                        ]);

                        /*
                         * Mengaktifkan jadwal draft.
                         */
                        Route::patch(
                            '/wfh-schedules/{wfhSchedule}/activate',
                            [WfhScheduleController::class, 'activate']
                        )->name('wfh-schedules.activate');

                        /*
                         * Membatalkan jadwal sebelum ada check-in.
                         */
                        Route::patch(
                            '/wfh-schedules/{wfhSchedule}/cancel',
                            [WfhScheduleController::class, 'cancel']
                        )->name('wfh-schedules.cancel');


                        /*
                         * Menggunakan kembali jadwal yang
                         * sebelumnya dibatalkan.
                         */
                        Route::patch(
                            '/wfh-schedules/{wfhSchedule}/restore',
                            [WfhScheduleController::class, 'restore']
                        )->name('wfh-schedules.restore');


                        /*
                         * Menambahkan personel ke jadwal.
                         */
                        Route::post(
                            '/wfh-schedules/{wfhSchedule}/members',
                            [
                                WfhScheduleMemberController::class,
                                'store',
                            ]
                        )->name('wfh-schedules.members.store');

                        /*
                         * Membatalkan keikutsertaan personel
                         * sebelum personel melakukan check-in.
                         */
                        Route::patch(
                            '/wfh-schedules/{wfhSchedule}/members/{wfhScheduleMember}/cancel',
                            [
                                WfhScheduleMemberController::class,
                                'cancel',
                            ]
                        )->name('wfh-schedules.members.cancel');
                    });

                /*
                 * =====================================================
                 * AREA PIMPINAN
                 * =====================================================
                 */
                Route::get(
                    '/pimpinan/dashboard',
                    [DashboardController::class, 'leader']
                )
                    ->middleware('role:Pimpinan')
                    ->name('leader.dashboard');

                /*
                 * =====================================================
                 * AREA PERSONEL
                 * =====================================================
                 */
                Route::get(
                    '/personel/dashboard',
                    [DashboardController::class, 'personnel']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.dashboard');


                /*
                 * Halaman check-in Personel.
                 */
                Route::get(
                    '/personel/presensi',
                    [AttendanceController::class, 'show']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.attendance.show');

                /*
                 * Menyimpan foto dan lokasi check-in.
                 */
                Route::post(
                    '/personel/presensi/{wfhScheduleMember}/check-in',
                    [AttendanceController::class, 'checkIn']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.attendance.checkin');


                /*
                 * Kelola rencana kerja pribadi Personel.
                 */
                Route::resource(
                    '/personel/rencana-kerja',
                    WorkItemController::class
                )
                    ->parameters([
                        'rencana-kerja' => 'workItem',
                    ])
                    ->except([
                        'show',
                    ])
                    ->middleware('role:Personel')
                    ->names([
                        'index' => 'personnel.work-items.index',
                        'create' => 'personnel.work-items.create',
                        'store' => 'personnel.work-items.store',
                        'edit' => 'personnel.work-items.edit',
                        'update' => 'personnel.work-items.update',
                        'destroy' => 'personnel.work-items.destroy',
                    ]);

                Route::get(
                    '/personel/pelaksanaan/{workItem}/edit',
                    [WorkExecutionController::class, 'edit']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.work-execution.edit');

                Route::put(
                    '/personel/pelaksanaan/{workItem}',
                    [WorkExecutionController::class, 'update']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.work-execution.update');

                Route::post(
                    '/personel/pelaksanaan/{workItem}/files',
                    [WorkExecutionController::class, 'uploadFile']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.work-execution.files.store');

                Route::get(
                    '/personel/pelaksanaan/{workItem}/files/{workItemFile}/download',
                    [WorkExecutionController::class, 'downloadFile']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.work-execution.files.download');

                Route::delete(
                    '/personel/pelaksanaan/{workItem}/files/{workItemFile}',
                    [WorkExecutionController::class, 'destroyFile']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.work-execution.files.destroy');


                /*
                 * Ringkasan dan pengiriman laporan kerja.
                 */
                Route::get(
                    '/personel/laporan',
                    [WorkReportController::class, 'show']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.report.show');

                Route::post(
                    '/personel/laporan/kirim',
                    [WorkReportController::class, 'submit']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.report.submit');

                /*
                 * Check-out Personel.
                 */
                Route::get(
                    '/personel/check-out',
                    [CheckoutController::class, 'show']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.checkout.show');

                Route::post(
                    '/personel/check-out/{wfhScheduleMember}',
                    [CheckoutController::class, 'store']
                )
                    ->middleware('role:Personel')
                    ->name('personnel.checkout.store');

            });
    });
});

/*
|--------------------------------------------------------------------------
| VERIFIKASI LAPORAN WFH
|--------------------------------------------------------------------------
|
| Route berikut digunakan oleh Admin dan Pimpinan untuk:
| 1. Melihat daftar laporan.
| 2. Membuka detail laporan.
| 3. Menyetujui laporan.
| 4. Meminta revisi laporan.
| 5. Mengunduh bukti pekerjaan.
|
*/

/*
 * Route verifikasi laporan untuk Admin.
 */
Route::middleware([
    'auth',
    'role:Admin',
])
    ->prefix('admin/laporan')
    ->name('admin.reports.')
    ->group(function () {
        /*
         * Menampilkan daftar laporan.
         */
        Route::get(
            '/',
            [
                WorkReportReviewController::class,
                'index',
            ]
        )->name('index');

        /*
         * Menampilkan detail laporan.
         */
        Route::get(
            '/{workReport}',
            [
                WorkReportReviewController::class,
                'show',
            ]
        )->name('show');

        /*
         * Menyetujui laporan.
         */
        Route::patch(
            '/{workReport}/approve',
            [
                WorkReportReviewController::class,
                'approve',
            ]
        )->name('approve');

        /*
         * Mengembalikan laporan untuk revisi.
         */
        Route::patch(
            '/{workReport}/revision',
            [
                WorkReportReviewController::class,
                'requestRevision',
            ]
        )->name('revision');

        /*
         * Mengunduh bukti PDF pekerjaan.
         */
        Route::get(
            '/{workReport}/files/{workItemFile}/download',
            [
                WorkReportReviewController::class,
                'downloadFile',
            ]
        )->name('files.download');
    });

/*
 * Route verifikasi laporan untuk Pimpinan.
 */
Route::middleware([
    'auth',
    'role:Pimpinan',
])
    ->prefix('pimpinan/laporan')
    ->name('leader.reports.')
    ->group(function () {
        /*
         * Menampilkan daftar laporan.
         */
        Route::get(
            '/',
            [
                WorkReportReviewController::class,
                'index',
            ]
        )->name('index');

        /*
         * Menampilkan detail laporan.
         */
        Route::get(
            '/{workReport}',
            [
                WorkReportReviewController::class,
                'show',
            ]
        )->name('show');

        /*
         * Menyetujui laporan.
         */
        Route::patch(
            '/{workReport}/approve',
            [
                WorkReportReviewController::class,
                'approve',
            ]
        )->name('approve');

        /*
         * Mengembalikan laporan untuk revisi.
         */
        Route::patch(
            '/{workReport}/revision',
            [
                WorkReportReviewController::class,
                'requestRevision',
            ]
        )->name('revision');

        /*
         * Mengunduh bukti PDF pekerjaan.
         */
        Route::get(
            '/{workReport}/files/{workItemFile}/download',
            [
                WorkReportReviewController::class,
                'downloadFile',
            ]
        )->name('files.download');
    });

/*
|--------------------------------------------------------------------------
| TUGAS PIMPINAN KEPADA PERSONEL
|--------------------------------------------------------------------------
|
| Pimpinan dapat membuat, melihat, dan membatalkan tugas
| yang diberikan kepada Personel pada jadwal WFH aktif.
|
*/
Route::middleware([
    'auth',
    'role:Pimpinan',
])
    ->prefix('pimpinan/tugas')
    ->name('leader.tasks.')
    ->group(function () {
        /*
         * Daftar tugas Pimpinan.
         */
        Route::get(
            '/',
            [
                LeaderTaskController::class,
                'index',
            ]
        )->name('index');

        /*
         * Form pemberian tugas.
         */
        Route::get(
            '/buat',
            [
                LeaderTaskController::class,
                'create',
            ]
        )->name('create');

        /*
         * Menyimpan tugas baru.
         */
        Route::post(
            '/',
            [
                LeaderTaskController::class,
                'store',
            ]
        )->name('store');

        /*
         * Membatalkan tugas.
         */
        Route::patch(
            '/{workItem}/cancel',
            [
                LeaderTaskController::class,
                'cancel',
            ]
        )->name('cancel');
    });

/*
|--------------------------------------------------------------------------
| MONITORING DAN REKAPITULASI WFH
|--------------------------------------------------------------------------
*/

/*
 * Monitoring untuk Admin.
 */
Route::middleware([
    'auth',
    'role:Admin',
])
    ->get(
        '/admin/monitoring',
        [
            MonitoringController::class,
            'index',
        ]
    )
    ->name('admin.monitoring.index');

/*
 * Monitoring untuk Pimpinan.
 */
Route::middleware([
    'auth',
    'role:Pimpinan',
])
    ->get(
        '/pimpinan/monitoring',
        [
            MonitoringController::class,
            'index',
        ]
    )
    ->name('leader.monitoring.index');

/*
|--------------------------------------------------------------------------
| PUSAT NOTIFIKASI
|--------------------------------------------------------------------------
|
| Route ini dapat digunakan oleh seluruh pengguna yang login.
|
*/
Route::middleware('auth')
    ->prefix('notifikasi')
    ->name('notifications.')
    ->group(function () {
        /*
         * Menampilkan daftar notifikasi.
         */
        Route::get(
            '/',
            [
                NotificationController::class,
                'index',
            ]
        )->name('index');

        /*
         * Membuka dan menandai satu notifikasi
         * sebagai sudah dibaca.
         */
        Route::get(
            '/{notification}/buka',
            [
                NotificationController::class,
                'open',
            ]
        )->name('open');

        /*
         * Menandai semua notifikasi sebagai dibaca.
         */
        Route::patch(
            '/baca-semua',
            [
                NotificationController::class,
                'markAllAsRead',
            ]
        )->name('read-all');
    });
