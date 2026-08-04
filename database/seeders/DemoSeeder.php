<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\WfhSchedule;
use App\Models\WfhScheduleMember;
use App\Models\WorkItem;
use App\Models\WorkReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Password seluruh akun demo.
     *
     * Password ini hanya digunakan untuk kebutuhan
     * simulasi dan presentasi aplikasi SIKERJA.
     */
    private const DEMO_PASSWORD = 'DemoSikerja#2026';

    /**
     * Penanda jadwal agar seeder dapat dijalankan
     * berulang tanpa membuat jadwal demo ganda.
     */
    private const SCHEDULE_MARKER =
        '[DEMO] Jadwal presentasi SIKERJA';

    /**
     * Menjalankan pembuatan data demo.
     */
    public function run(): void
    {
        /*
         * Seluruh proses dijalankan dalam transaksi.
         *
         * Jika ada satu proses gagal, semua perubahan
         * dari seeder ini dibatalkan secara otomatis.
         */
        DB::transaction(function (): void {
            /*
             * Memastikan role dan unit dasar tersedia.
             */
            $this->call([
                RoleSeeder::class,
                UnitSeeder::class,
            ]);

            $adminRole = Role::query()
                ->where('name', 'Admin')
                ->firstOrFail();

            $leaderRole = Role::query()
                ->where('name', 'Pimpinan')
                ->firstOrFail();

            $personnelRole = Role::query()
                ->where('name', 'Personel')
                ->firstOrFail();

            $unit = Unit::query()
                ->where('code', 'DITKUMAD')
                ->firstOrFail();

            /*
             * =====================================================
             * MEMBUAT AKUN DEMO
             * =====================================================
             */
            $admin = $this->createDemoUser(
                loginId: 'DEMOADMIN',
                roleId: $adminRole->id,
                unitId: $unit->id,
                name: 'Administrator Demo',
                rank: 'Admin',
                position: 'Administrator SIKERJA'
            );

            $leader = $this->createDemoUser(
                loginId: 'DEMOPIMPINAN',
                roleId: $leaderRole->id,
                unitId: $unit->id,
                name: 'Pimpinan Demo',
                rank: 'Kolonel Chk',
                position: 'Pimpinan Unit Kerja'
            );

            $personnelOne = $this->createDemoUser(
                loginId: 'DEMOPER001',
                roleId: $personnelRole->id,
                unitId: $unit->id,
                name: 'Andi Pratama',
                rank: 'Serka',
                position: 'Bamin Administrasi'
            );

            $personnelTwo = $this->createDemoUser(
                loginId: 'DEMOPER002',
                roleId: $personnelRole->id,
                unitId: $unit->id,
                name: 'Budi Santoso',
                rank: 'Sertu',
                position: 'Bamin Hukum'
            );

            $personnelThree = $this->createDemoUser(
                loginId: 'DEMOPER003',
                roleId: $personnelRole->id,
                unitId: $unit->id,
                name: 'Citra Lestari',
                rank: 'Serda',
                position: 'Operator Data'
            );

            $personnelFour = $this->createDemoUser(
                loginId: 'DEMOPER004',
                roleId: $personnelRole->id,
                unitId: $unit->id,
                name: 'Dedi Kurniawan',
                rank: 'Praka',
                position: 'Staf Administrasi'
            );

            $personnelFive = $this->createDemoUser(
                loginId: 'DEMOPER005',
                roleId: $personnelRole->id,
                unitId: $unit->id,
                name: 'Eka Saputra',
                rank: 'Pratu',
                position: 'Staf Dokumentasi'
            );

            /*
             * =====================================================
             * MEMBUAT JADWAL DEMO
             * =====================================================
             */
            $schedule = $this->createDemoSchedule(
                admin: $admin
            );

            $scheduleDate = Carbon::parse(
                $schedule->wfh_date,
                'Asia/Jakarta'
            )->startOfDay();

            /*
             * Membuat waktu simulasi berdasarkan
             * tanggal jadwal demo.
             */
            $checkinTime = $scheduleDate
                ->copy()
                ->setTime(8, 0);

            $checkoutTime = $scheduleDate
                ->copy()
                ->setTime(16, 15);

            /*
             * =====================================================
             * SKENARIO 1
             * Laporan menunggu verifikasi.
             * =====================================================
             */
            $memberOne = $this->createMember(
                schedule: $schedule,
                user: $personnelOne,
                admin: $admin,
                status: 'present'
            );

            $this->createAttendance(
                member: $memberOne,
                checkinAt: $checkinTime,
                checkoutAt: $checkoutTime,
                attendanceStatus: 'present'
            );

            $reportOne = $this->createReport(
                member: $memberOne,
                status: 'waiting_verification',
                submittedAt: $checkoutTime,
                verifier: null,
                verificationNote: null,
                isLocked: true
            );

            $this->createWorkItem(
                report: $reportOne,
                creator: $personnelOne,
                sourceType: 'personal_plan',
                title: 'Penyusunan rekap administrasi mingguan',
                description:
                    'Menyusun rekap administrasi dan surat masuk selama satu minggu.',
                targetResult:
                    'Dokumen rekap administrasi tersedia dan siap diperiksa.',
                status: 'completed',
                progress: 100
            );

            $taskOne = $this->createWorkItem(
                report: $reportOne,
                creator: $leader,
                sourceType: 'leader_task',
                title: 'Memeriksa kelengkapan data personel',
                description:
                    'Memeriksa kesesuaian data personel pada dokumen rekap.',
                targetResult:
                    'Data personel telah diperiksa dan diperbaiki.',
                status: 'completed',
                progress: 100
            );

            /*
             * =====================================================
             * SKENARIO 2
             * Laporan perlu direvisi.
             * =====================================================
             */
            $memberTwo = $this->createMember(
                schedule: $schedule,
                user: $personnelTwo,
                admin: $admin,
                status: 'present'
            );

            $this->createAttendance(
                member: $memberTwo,
                checkinAt: $checkinTime->copy()->addMinutes(7),
                checkoutAt: $checkoutTime->copy()->addMinutes(5),
                attendanceStatus: 'present'
            );

            $reportTwo = $this->createReport(
                member: $memberTwo,
                status: 'needs_revision',
                submittedAt: $checkoutTime,
                verifier: $leader,
                verificationNote:
                    'Mohon lengkapi hasil pekerjaan dan uraian tindak lanjut.',
                isLocked: false
            );

            $this->createWorkItem(
                report: $reportTwo,
                creator: $personnelTwo,
                sourceType: 'personal_plan',
                title: 'Penyusunan konsep bahan laporan hukum',
                description:
                    'Menyusun konsep awal bahan laporan kegiatan hukum.',
                targetResult:
                    'Konsep laporan hukum tersusun sesuai format.',
                status: 'completed',
                progress: 100,
                followUpPlan:
                    'Melengkapi lampiran dan uraian tindak lanjut.'
            );

            /*
             * =====================================================
             * SKENARIO 3
             * Laporan sudah disetujui.
             * =====================================================
             */
            $memberThree = $this->createMember(
                schedule: $schedule,
                user: $personnelThree,
                admin: $admin,
                status: 'present'
            );

            $this->createAttendance(
                member: $memberThree,
                checkinAt: $checkinTime->copy()->subMinutes(5),
                checkoutAt: $checkoutTime->copy()->subMinutes(10),
                attendanceStatus: 'present'
            );

            $reportThree = $this->createReport(
                member: $memberThree,
                status: 'approved',
                submittedAt: $checkoutTime->copy()->subMinutes(10),
                verifier: $leader,
                verificationNote:
                    'Laporan lengkap dan hasil pekerjaan sesuai sasaran.',
                isLocked: true
            );

            $this->createWorkItem(
                report: $reportThree,
                creator: $personnelThree,
                sourceType: 'personal_plan',
                title: 'Pemutakhiran data dokumentasi kegiatan',
                description:
                    'Memperbarui data dan daftar dokumentasi kegiatan satuan.',
                targetResult:
                    'Data dokumentasi tersusun dan telah diperbarui.',
                status: 'completed',
                progress: 100
            );

            /*
             * =====================================================
             * SKENARIO 4
             * Sudah check-in dan pekerjaan masih berlangsung.
             * =====================================================
             */
            $memberFour = $this->createMember(
                schedule: $schedule,
                user: $personnelFour,
                admin: $admin,
                status: 'present'
            );

            $this->createAttendance(
                member: $memberFour,
                checkinAt: $checkinTime->copy()->addMinutes(5),
                checkoutAt: null,
                attendanceStatus: 'incomplete'
            );

            $reportFour = $this->createReport(
                member: $memberFour,
                status: 'draft',
                submittedAt: null,
                verifier: null,
                verificationNote: null,
                isLocked: false
            );

            $taskFour = $this->createWorkItem(
                report: $reportFour,
                creator: $leader,
                sourceType: 'leader_task',
                title: 'Menyiapkan bahan paparan pimpinan',
                description:
                    'Menyiapkan bahan paparan hasil pelaksanaan WFH.',
                targetResult:
                    'Bahan paparan siap digunakan oleh Pimpinan.',
                status: 'in_progress',
                progress: 60,
                obstacle:
                    'Menunggu satu data pendukung dari bagian terkait.',
                followUpPlan:
                    'Melengkapi bahan paparan setelah data diterima.'
            );

            /*
             * =====================================================
             * SKENARIO 5
             * Terjadwal tetapi belum melakukan check-in.
             * =====================================================
             */
            $memberFive = $this->createMember(
                schedule: $schedule,
                user: $personnelFive,
                admin: $admin,
                status: 'scheduled'
            );

            $reportFive = $this->createReport(
                member: $memberFive,
                status: 'draft',
                submittedAt: null,
                verifier: null,
                verificationNote: null,
                isLocked: false
            );

            $this->createWorkItem(
                report: $reportFive,
                creator: $personnelFive,
                sourceType: 'personal_plan',
                title: 'Penyusunan daftar dokumentasi',
                description:
                    'Menyusun daftar dokumentasi kegiatan yang akan diperbarui.',
                targetResult:
                    'Daftar kebutuhan dokumentasi tersedia.',
                status: 'not_started',
                progress: 0
            );

            /*
             * =====================================================
             * MEMBUAT NOTIFIKASI DEMO
             * =====================================================
             */
            $this->createNotification(
                user: $leader,
                type: 'work_report_submitted',
                title: 'Laporan Menunggu Verifikasi',
                message:
                    'Laporan WFH Andi Pratama menunggu pemeriksaan.',
                relatedType: WorkReport::class,
                relatedId: $reportOne->id
            );

            $this->createNotification(
                user: $personnelTwo,
                type: 'work_report_revision',
                title: 'Laporan WFH Perlu Revisi',
                message:
                    'Laporan Anda perlu dilengkapi sesuai catatan Pimpinan.',
                relatedType: WorkReport::class,
                relatedId: $reportTwo->id
            );

            $this->createNotification(
                user: $personnelThree,
                type: 'work_report_approved',
                title: 'Laporan WFH Disetujui',
                message:
                    'Laporan WFH Anda telah diperiksa dan disetujui.',
                relatedType: WorkReport::class,
                relatedId: $reportThree->id
            );

            $this->createNotification(
                user: $personnelFour,
                type: 'leader_task',
                title: 'Tugas Baru dari Pimpinan',
                message:
                    'Anda menerima tugas menyiapkan bahan paparan Pimpinan.',
                relatedType: WorkItem::class,
                relatedId: $taskFour->id
            );

            $this->createNotification(
                user: $personnelOne,
                type: 'leader_task',
                title: 'Tugas dari Pimpinan',
                message:
                    'Tugas pemeriksaan kelengkapan data telah diberikan.',
                relatedType: WorkItem::class,
                relatedId: $taskOne->id
            );
        });

        /*
         * Menampilkan informasi akun setelah seeder selesai.
         */
        $this->command?->newLine();

        $this->command?->info(
            'Data demo SIKERJA berhasil dibuat.'
        );

        $this->command?->table(
            [
                'Role',
                'Login ID',
                'Password',
                'Skenario',
            ],
            [
                [
                    'Admin',
                    'DEMOADMIN',
                    self::DEMO_PASSWORD,
                    'Pengelolaan sistem',
                ],
                [
                    'Pimpinan',
                    'DEMOPIMPINAN',
                    self::DEMO_PASSWORD,
                    'Monitoring dan verifikasi',
                ],
                [
                    'Personel',
                    'DEMOPER001',
                    self::DEMO_PASSWORD,
                    'Menunggu verifikasi',
                ],
                [
                    'Personel',
                    'DEMOPER002',
                    self::DEMO_PASSWORD,
                    'Perlu revisi',
                ],
                [
                    'Personel',
                    'DEMOPER003',
                    self::DEMO_PASSWORD,
                    'Laporan disetujui',
                ],
                [
                    'Personel',
                    'DEMOPER004',
                    self::DEMO_PASSWORD,
                    'Pekerjaan berlangsung',
                ],
                [
                    'Personel',
                    'DEMOPER005',
                    self::DEMO_PASSWORD,
                    'Belum check-in',
                ],
            ]
        );
    }

    /**
     * Membuat atau memperbarui akun demo.
     */
    private function createDemoUser(
        string $loginId,
        int $roleId,
        int $unitId,
        string $name,
        string $rank,
        string $position
    ): User {
        return User::query()->updateOrCreate(
            [
                'login_id' => $loginId,
            ],
            [
                'role_id' => $roleId,
                'unit_id' => $unitId,
                'name' => $name,
                'rank' => $rank,
                'position' => $position,
                'password' => Hash::make(
                    self::DEMO_PASSWORD
                ),
                'must_change_password' => false,
                'is_active' => true,
                'last_login_at' => null,
            ]
        );
    }

    /**
     * Membuat jadwal WFH khusus demo.
     */
    private function createDemoSchedule(
        User $admin
    ): WfhSchedule {
        $schedule = WfhSchedule::query()
            ->firstOrNew([
                'notes' => self::SCHEDULE_MARKER,
            ]);

        /*
         * Mencari tanggal yang belum digunakan oleh
         * jadwal lain agar tidak berbenturan.
         */
        $candidateDate = Carbon::now(
            'Asia/Jakarta'
        )->startOfDay();

        while (
            WfhSchedule::query()
                ->whereDate('wfh_date', $candidateDate)
                ->when(
                    $schedule->exists,
                    function ($query) use ($schedule) {
                        $query->where(
                            'id',
                            '!=',
                            $schedule->id
                        );
                    }
                )
                ->exists()
        ) {
            $candidateDate->addDay();
        }

        $schedule->wfh_date = $candidateDate;
        $schedule->status = 'active';
        $schedule->created_by = $admin->id;
        $schedule->is_all_personnel = false;
        $schedule->notes = self::SCHEDULE_MARKER;
        $schedule->activated_at = Carbon::now(
            'Asia/Jakarta'
        );

        $schedule->save();

        return $schedule;
    }

    /**
     * Membuat anggota jadwal WFH.
     */
    private function createMember(
        WfhSchedule $schedule,
        User $user,
        User $admin,
        string $status
    ): WfhScheduleMember {
        return WfhScheduleMember::query()
            ->updateOrCreate(
                [
                    'schedule_id' => $schedule->id,
                    'user_id' => $user->id,
                ],
                [
                    'member_status' => $status,
                    'added_by' => $admin->id,
                    'is_schedule_change' => false,
                    'change_reason' => null,
                    'added_at' => Carbon::now(
                        'Asia/Jakarta'
                    ),
                    'checkin_deadline' => Carbon::parse(
                        $schedule->wfh_date,
                        'Asia/Jakarta'
                    )->setTime(8, 30),
                    'cancelled_at' => null,
                ]
            );
    }

    /**
     * Membuat data presensi demo.
     */
    private function createAttendance(
        WfhScheduleMember $member,
        ?Carbon $checkinAt,
        ?Carbon $checkoutAt,
        ?string $attendanceStatus
    ): Attendance {
        return Attendance::query()->updateOrCreate(
            [
                'schedule_member_id' => $member->id,
            ],
            [
                'checkin_at' => $checkinAt,
                'checkin_status' => null,
                'checkin_reason' => null,
                'checkin_latitude' => -6.175392,
                'checkin_longitude' => 106.827153,
                'checkin_photo_path' => null,
                'checkin_photo_expires_at' => null,
                'checkin_photo_deleted_at' => null,

                'checkout_at' => $checkoutAt,
                'checkout_status' => null,
                'checkout_reason' => null,
                'checkout_latitude' => $checkoutAt
                    ? -6.175392
                    : null,
                'checkout_longitude' => $checkoutAt
                    ? 106.827153
                    : null,
                'checkout_photo_path' => null,
                'checkout_photo_expires_at' => null,
                'checkout_photo_deleted_at' => null,

                'attendance_status' =>
                    $attendanceStatus,
            ]
        );
    }

    /**
     * Membuat laporan kerja demo.
     */
    private function createReport(
        WfhScheduleMember $member,
        string $status,
        ?Carbon $submittedAt,
        ?User $verifier,
        ?string $verificationNote,
        bool $isLocked
    ): WorkReport {
        $verifiedAt = $verifier
            ? Carbon::parse(
                $member->schedule->wfh_date,
                'Asia/Jakarta'
            )->setTime(17, 0)
            : null;

        return WorkReport::query()->updateOrCreate(
            [
                'schedule_member_id' => $member->id,
            ],
            [
                'status' => $status,
                'submitted_at' => $submittedAt,

                'last_change_reason' =>
                    $status === 'needs_revision'
                        ? $verificationNote
                        : null,

                'last_changed_at' =>
                    $status === 'needs_revision'
                        ? $verifiedAt
                        : null,

                'verified_by' => $verifier?->id,
                'verified_at' => $verifiedAt,

                'verification_note' =>
                    $verificationNote,

                'completed_offline_at' => null,

                'locked_at' => $isLocked
                    ? ($submittedAt ?? $verifiedAt)
                    : null,

                'is_locked' => $isLocked,
            ]
        );
    }

    /**
     * Membuat pekerjaan demo.
     */
    private function createWorkItem(
        WorkReport $report,
        User $creator,
        string $sourceType,
        string $title,
        string $description,
        string $targetResult,
        string $status,
        int $progress,
        ?string $obstacle = null,
        ?string $followUpPlan = null
    ): WorkItem {
        return WorkItem::query()->updateOrCreate(
            [
                'report_id' => $report->id,
                'source_type' => $sourceType,
                'title' => $title,
            ],
            [
                'created_by' => $creator->id,
                'description' => $description,
                'target_result' => $targetResult,
                'status' => $status,
                'progress' => $progress,
                'obstacle' => $obstacle,
                'follow_up_plan' => $followUpPlan,
                'continue_offline' => false,
                'cancelled_by' => null,
                'cancelled_at' => null,

                'assigned_at' =>
                    $sourceType === 'leader_task'
                        ? Carbon::now('Asia/Jakarta')
                        : null,
            ]
        );
    }

    /**
     * Membuat notifikasi demo tanpa menghasilkan
     * data ganda ketika seeder dijalankan ulang.
     */
    private function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        string $relatedType,
        int $relatedId
    ): AppNotification {
        return AppNotification::query()
            ->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $type,
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                    'title' => $title,
                ],
                [
                    'message' => $message,
                    'is_read' => false,
                    'read_at' => null,
                ]
            );
    }
}
