<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\WfhSchedule;
use App\Models\WfhScheduleMember;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MonitoringPdfController extends Controller
{
    /**
     * Mengunduh rekap presensi dan hasil kinerja Personel
     * berdasarkan jadwal serta filter pada halaman monitoring.
     */
    public function download(Request $request)
    {
        /*
         * Mengambil filter dari URL.
         *
         * Contoh:
         * ?schedule_id=1&unit_id=2&search=Dwi
         */
        $scheduleId = $request->integer('schedule_id');
        $unitId = $request->integer('unit_id');

        $search = trim(
            (string) $request->input('search')
        );

        /*
         * Mengambil seluruh jadwal, dimulai dari tanggal terbaru.
         */
        $schedules = WfhSchedule::query()
            ->orderByDesc('wfh_date')
            ->get();

        /*
         * Menentukan jadwal yang akan dicetak.
         *
         * Prioritas:
         * 1. Jadwal yang dipilih pada filter.
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
         * Menghentikan proses apabila belum ada jadwal WFH.
         */
        abort_if(
            ! $selectedSchedule,
            404,
            'Jadwal WFH belum tersedia.'
        );

        /*
         * Mengambil anggota jadwal beserta data yang dibutuhkan:
         *
         * - Identitas Personel.
         * - Unit kerja.
         * - Presensi.
         * - Laporan kerja.
         * - Daftar pekerjaan.
         */
        $membersQuery = WfhScheduleMember::query()
            ->with([
                'user.unit',
                'schedule',
                'attendance',
                'workReport.items',
            ])

            /*
             * Personel yang dibatalkan tidak ikut dicetak.
             */
            ->whereNull('cancelled_at')

            /*
             * Hanya mengambil Personel dari jadwal terpilih.
             */
            ->where(
                'schedule_id',
                $selectedSchedule->id
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
             * Filter pencarian Personel.
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
         * PDF tidak menggunakan pagination.
         * Semua Personel sesuai filter akan dicetak.
         */
        $members = $membersQuery->get();

        /*
         * Menghitung statistik presensi dan laporan.
         */
        $summary = [
            /*
             * Jumlah seluruh Personel.
             */
            'total' => $members->count(),

            /*
             * Jumlah Personel yang sudah check-in.
             */
            'checked_in' => $members
                ->filter(
                    fn ($member) =>
                        filled(
                            $member->attendance?->checkin_at
                        )
                )
                ->count(),

            /*
             * Jumlah Personel yang belum check-in.
             */
            'not_checked_in' => $members
                ->filter(
                    fn ($member) =>
                        blank(
                            $member->attendance?->checkin_at
                        )
                )
                ->count(),

            /*
             * Jumlah Personel yang sudah check-out.
             */
            'checked_out' => $members
                ->filter(
                    fn ($member) =>
                        filled(
                            $member->attendance?->checkout_at
                        )
                )
                ->count(),

            /*
             * Jumlah Personel yang belum check-out.
             */
            'not_checked_out' => $members
                ->filter(
                    fn ($member) =>
                        blank(
                            $member->attendance?->checkout_at
                        )
                )
                ->count(),

            /*
             * Jumlah laporan yang menunggu pemeriksaan.
             */
            'waiting_verification' => $members
                ->filter(
                    fn ($member) =>
                        $member->workReport?->status
                        === 'waiting_verification'
                )
                ->count(),

            /*
             * Jumlah laporan yang perlu diperbaiki.
             */
            'needs_revision' => $members
                ->filter(
                    fn ($member) =>
                        $member->workReport?->status
                        === 'needs_revision'
                )
                ->count(),

            /*
             * Jumlah laporan yang sudah disetujui.
             */
            'approved' => $members
                ->filter(
                    fn ($member) =>
                        $member->workReport?->status
                        === 'approved'
                )
                ->count(),

            /*
             * Jumlah laporan yang belum dikirim.
             */
            'not_submitted' => $members
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

            /*
             * Menghitung seluruh pekerjaan.
             */
            'total_items' => $members->sum(
                fn ($member) =>
                    $member
                        ->workReport
                        ?->items
                        ->count()
                    ?? 0
            ),

            /*
             * Menghitung pekerjaan berstatus selesai.
             */
            'completed_items' => $members->sum(
                fn ($member) =>
                    $member->workReport
                        ?->items
                        ->where(
                            'status',
                            'completed'
                        )
                        ->count()
                    ?? 0
            ),
        ];

        /*
         * Mengambil unit yang dipilih pada filter.
         *
         * Jika unit_id kosong, PDF mencetak semua unit.
         */
        $selectedUnit = $unitId
            ? Unit::query()->find($unitId)
            : null;

        /*
         * Menyiapkan logo agar dapat dibaca DOMPDF.
         *
         * Logo diubah menjadi base64 sehingga tidak
         * bergantung pada URL atau server Vite.
         */
        $logoDataUri = null;

        $logoPath = public_path(
            'images/logo-sikerja.png'
        );

        if (File::exists($logoPath)) {
            $mimeType = File::mimeType($logoPath);

            $logoDataUri =
                'data:'
                . $mimeType
                . ';base64,'
                . base64_encode(
                    File::get($logoPath)
                );
        }

        /*
         * Menyimpan informasi pembuat dokumen.
         */
        $generatedBy = $request->user();
        $generatedAt = now();

        /*
         * Membuat nama file PDF berdasarkan tanggal WFH.
         */
        $fileName = sprintf(
            'rekap-kinerja-wfh-%s.pdf',
            $selectedSchedule
                ->wfh_date
                ->format('Y-m-d')
        );

        /*
         * Membuat PDF dari Blade.
         *
         * Landscape digunakan agar tabel hasil kinerja
         * memiliki ruang yang lebih luas.
         */
        return Pdf::loadView(
            'monitoring.pdf',
            compact(
                'members',
                'summary',
                'selectedSchedule',
                'selectedUnit',
                'search',
                'generatedBy',
                'generatedAt',
                'logoDataUri'
            )
        )
            ->setPaper('a4', 'landscape')
            ->download($fileName);
    }
}
