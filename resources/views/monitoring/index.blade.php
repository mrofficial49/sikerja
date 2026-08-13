@extends('layouts.app')

@section('title', 'Monitoring dan Rekap WFH')

@section('content')
<div class="container-fluid py-4">

    {{-- Judul halaman --}}
    <div
        class="d-flex flex-column flex-md-row
               justify-content-between align-items-md-center
               gap-3 mb-4"
    >
        <div>
            <h1 class="h3 fw-bold mb-1">
                Monitoring dan Rekap WFH
            </h1>

            <p class="text-secondary mb-0">
                Pantau presensi, pekerjaan, dan laporan Personel.
            </p>
        </div>

       @if ($selectedSchedule)
    <div class="d-flex flex-wrap align-items-center gap-2">
        {{-- Informasi tanggal jadwal yang dipilih. --}}
        <span class="badge text-bg-primary fs-6">
            {{
                $selectedSchedule
                    ->wfh_date
                    ->translatedFormat('d F Y')
            }}
        </span>

        {{-- Tombol untuk mengunduh rekap PDF.
             Filter halaman monitoring ikut diterapkan. --}}
        <a
            href="{{
                route(
                    $routePrefix . '.pdf',
                    [
                        'schedule_id' =>
                            $selectedSchedule->id,

                        'unit_id' =>
                            $unitId ?: null,

                        'search' =>
                            $search !== ''
                                ? $search
                                : null,
                    ]
                )
            }}"
            class="btn btn-danger"
        >
            Ekspor Rekap PDF
        </a>
    </div>
@endif
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form
                method="GET"
                action="{{ route($routePrefix . '.index') }}"
                class="row g-3"
            >
                <div class="col-lg-4">
                    <label
                        for="schedule_id"
                        class="form-label fw-semibold"
                    >
                        Jadwal WFH
                    </label>

                    <select
                        id="schedule_id"
                        name="schedule_id"
                        class="form-select"
                    >
                        @foreach ($schedules as $schedule)
                            <option
                                value="{{ $schedule->id }}"
                                @selected(
                                    $selectedSchedule?->id
                                    === $schedule->id
                                )
                            >
                                {{
                                    $schedule
                                        ->wfh_date
                                        ->translatedFormat(
                                            'd F Y'
                                        )
                                }}
                                — {{ ucfirst($schedule->status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3">
                    <label
                        for="unit_id"
                        class="form-label fw-semibold"
                    >
                        Unit Kerja
                    </label>

                    <select
                        id="unit_id"
                        name="unit_id"
                        class="form-select"
                    >
                        <option value="">
                            Semua Unit
                        </option>

                        @foreach ($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected(
                                    (string) $unitId
                                    === (string) $unit->id
                                )
                            >
                                {{ $unit->code ?? $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3">
                    <label
                        for="search"
                        class="form-label fw-semibold"
                    >
                        Cari Personel
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Nama, NRP/NIP, pangkat"
                    >
                </div>

                <div class="col-lg-2 d-flex align-items-end gap-2">
                    <button
                        type="submit"
                        class="btn btn-primary flex-grow-1"
                    >
                        Tampilkan
                    </button>

                    <a
                        href="{{ route($routePrefix . '.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Ringkasan statistik utama --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Total Personel
                    </small>

                    <div class="display-6 fw-bold">
                        {{ $summary['total'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Sudah Check-in
                    </small>

                    <div class="display-6 fw-bold text-success">
                        {{ $summary['checked_in'] }}
                    </div>

                    <small class="text-danger">
                        {{ $summary['not_checked_in'] }}
                        belum check-in
                    </small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Sudah Check-out
                    </small>

                    <div class="display-6 fw-bold text-primary">
                        {{ $summary['checked_out'] }}
                    </div>

                    <small class="text-warning">
                        {{ $summary['not_checked_out'] }}
                        belum check-out
                    </small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Laporan Disetujui
                    </small>

                    <div class="display-6 fw-bold text-success">
                        {{ $summary['approved'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan status laporan --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Menunggu Verifikasi
                    </small>

                    <div class="h2 fw-bold mb-0">
                        {{ $summary['waiting_verification'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Perlu Revisi
                    </small>

                    <div class="h2 fw-bold mb-0">
                        {{ $summary['needs_revision'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-secondary shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Belum Mengirim Laporan
                    </small>

                    <div class="h2 fw-bold mb-0">
                        {{ $summary['not_submitted'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel rincian Personel --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h2 class="h5 fw-bold mb-0">
                Rincian Personel
            </h2>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">No.</th>
                            <th>Personel</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Pekerjaan</th>
                            <th>Status Laporan</th>
                            <th class="text-end px-4">
                                Tindakan
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($members as $member)
    @php
        $person = $member->user;
        $attendance = $member->attendance;
        $report = $member->workReport;

        /*
         * Mengambil seluruh pekerjaan milik laporan.
         *
         * Jika laporan belum ada, gunakan collection kosong
         * agar halaman Monitoring tidak error.
         */
        $workItems = $report?->items ?? collect();

        /*
         * Pekerjaan cancelled tetap menjadi histori,
         * tetapi tidak dihitung sebagai pekerjaan aktif.
         */
        $activeWorkItems = $workItems->where(
            'status',
            '!=',
            'cancelled'
        );

        /*
         * Menghitung pekerjaan Belum Dimulai.
         *
         * Pekerjaan yang ditandai continue_offline
         * akan dihitung sebagai Dilanjutkan Offline,
         * sehingga tidak dihitung dua kali.
         */
        $notStartedCount = $activeWorkItems
            ->where('status', 'not_started')
            ->where('continue_offline', false)
            ->count();

        /*
         * Menghitung pekerjaan Sedang Dikerjakan.
         */
        $inProgressCount = $activeWorkItems
            ->where('status', 'in_progress')
            ->where('continue_offline', false)
            ->count();

        /*
         * Menghitung pekerjaan Terkendala.
         */
        $blockedCount = $activeWorkItems
            ->where('status', 'blocked')
            ->where('continue_offline', false)
            ->count();

        /*
         * Menghitung pekerjaan yang sudah selesai.
         */
        $completedCount = $activeWorkItems
            ->where('status', 'completed')
            ->count();

        /*
         * Menghitung pekerjaan yang belum selesai,
         * tetapi akan diteruskan secara offline.
         */
        $offlineCount = $activeWorkItems
            ->filter(function ($item) {
                return $item->continue_offline
                    && ! in_array(
                        $item->status,
                        [
                            'completed',
                            'cancelled',
                        ],
                        true
                    );
            })
            ->count();

        /*
         * Menghitung pekerjaan yang dibatalkan.
         * Tetap ditampilkan sebagai histori.
         */
        $cancelledCount = $workItems
            ->where('status', 'cancelled')
            ->count();

        $reportClass = match (
            $report?->status
        ) {
            'waiting_verification' =>
                'warning',

            'needs_revision' =>
                'danger',

            'approved' =>
                'success',

            'completed_offline' =>
                'info',

            default =>
                'secondary',
        };

        $reportLabel = match (
            $report?->status
        ) {
            'draft' =>
                'Draft',

            'waiting_verification' =>
                'Menunggu Verifikasi',

            'needs_revision' =>
                'Perlu Revisi',

            'approved' =>
                'Disetujui',

            'incomplete' =>
                'Belum Lengkap',

            'completed_offline' =>
                'Selesai Offline',

            default =>
                'Belum Ada Laporan',
        };
    @endphp


                            <tr>
                                <td class="px-4">
                                    {{
                                        $members->firstItem()
                                        + $loop->index
                                    }}
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $person?->name ?? '-' }}
                                    </div>

                                    <small class="text-secondary">
                                        {{ $person?->login_id ?? '-' }}
                                        ·
                                        {{ $person?->rank ?? '-' }}
                                        ·
                                        {{ $person?->unit?->code ?? '-' }}
                                    </small>
                                </td>

                                <td>
                                    @if ($attendance?->checkin_at)
                                        <span class="badge text-bg-success">
                                            Sudah
                                        </span>

                                        <div class="small mt-1">
                                            {{
                                                $attendance
                                                    ->checkin_at
                                                    ->format('H:i')
                                            }}
                                        </div>
                                    @else
                                        <span class="badge text-bg-danger">
                                            Belum
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($attendance?->checkout_at)
                                        <span class="badge text-bg-success">
                                            Sudah
                                        </span>

                                        <div class="small mt-1">
                                            {{
                                                $attendance
                                                    ->checkout_at
                                                    ->format('H:i')
                                            }}
                                        </div>
                                    @else
                                        <span class="badge text-bg-warning">
                                            Belum
                                        </span>
                                    @endif
                                </td>

                               <td style="min-width: 210px;">
    {{-- Jumlah seluruh pekerjaan --}}
    <div class="fw-semibold mb-2">
        {{ $workItems->count() }}
        pekerjaan
    </div>

    @if ($workItems->isEmpty())

        <span class="badge text-bg-secondary">
            Belum Ada Pekerjaan
        </span>

    @else

        <div class="d-flex flex-wrap gap-1">

            {{-- Pekerjaan selesai --}}
            @if ($completedCount > 0)
                <span class="badge text-bg-success">
                    {{ $completedCount }}
                    Selesai
                </span>
            @endif

            {{-- Sedang dikerjakan --}}
            @if ($inProgressCount > 0)
                <span class="badge text-bg-primary">
                    {{ $inProgressCount }}
                    Dikerjakan
                </span>
            @endif

            {{-- Terkendala --}}
            @if ($blockedCount > 0)
                <span class="badge text-bg-danger">
                    {{ $blockedCount }}
                    Terkendala
                </span>
            @endif

            {{-- Belum dimulai --}}
            @if ($notStartedCount > 0)
                <span class="badge text-bg-warning">
                    {{ $notStartedCount }}
                    Belum Dimulai
                </span>
            @endif

            {{-- Dilanjutkan di luar sesi WFH --}}
            @if ($offlineCount > 0)
                <span class="badge text-bg-info">
                    {{ $offlineCount }}
                    Dilanjutkan Offline
                </span>
            @endif

            {{-- Histori pekerjaan dibatalkan --}}
            @if ($cancelledCount > 0)
                <span class="badge text-bg-secondary">
                    {{ $cancelledCount }}
                    Dibatalkan
                </span>
            @endif

        </div>

    @endif
</td>

                                <td>
                                    <span
                                        class="badge
                                               text-bg-{{ $reportClass }}"
                                    >
                                        {{ $reportLabel }}
                                    </span>
                                </td>

                                <td class="text-end px-4">
                                    {{-- Tombol hanya muncul apabila Personel
     sudah mempunyai data presensi. --}}
@if ($member->attendance)
    <a
        href="{{
            route(
                'attendance.evidence.show',
                [
                    'attendance' =>
                        $member->attendance->id,
                ]
            )
        }}"
        class="btn btn-sm btn-outline-success"
    >
        Foto & GPS
    </a>
@endif
                                    @if (
                                        $report
                                        && $report->status !== 'draft'
                                    )
                                        <a
                                            href="{{
                                                route(
                                                    $reportRoutePrefix
                                                        . '.show',
                                                    $report
                                                )
                                            }}"
                                            class="btn btn-sm
                                                   btn-outline-primary"
                                        >
                                            Lihat Laporan
                                        </a>
                                    @else
                                        <span class="text-secondary">
                                            -
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center py-5
                                           text-secondary"
                                >
                                    Data Personel tidak tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($members->hasPages())
            <div class="card-footer bg-white">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
