@extends('layouts.app')

@section('title', 'Dashboard Personel - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Dashboard Personel
    </h1>

    <p class="text-secondary mb-0">
        Selamat datang, {{ $user->name }}.
    </p>
</div>

@if ($testMode)
    <div class="alert alert-warning">
        Mode pengujian presensi lokal sedang aktif.
    </div>
@endif

{{-- Ringkasan utama --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Status Jadwal
                </small>

                <div class="h4 fw-bold mt-2 mb-0">
                    {{ $membership ? 'Terjadwal' : 'Tidak Ada' }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Check-in
                </small>

                <div class="h4 fw-bold mt-2 mb-0">
                    @if ($membership?->attendance?->checkin_at)
                        <span class="text-success">
                            Selesai
                        </span>
                    @elseif ($membership)
                        <span class="text-warning">
                            Belum
                        </span>
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Check-out
                </small>

                <div class="h4 fw-bold mt-2 mb-0">
                    @if ($membership?->attendance?->checkout_at)
                        <span class="text-success">
                            Selesai
                        </span>
                    @elseif ($membership?->attendance?->checkin_at)
                        <span class="text-warning">
                            Belum
                        </span>
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Notifikasi Baru
                </small>

                <div class="display-6 fw-bold text-danger">
                    {{ $unreadNotifications }}
                </div>
            </div>
        </div>
    </div>
</div>

@if ($membership)
    @php
        $report = $membership->workReport;

        $reportStatusLabel = match ($report?->status) {
            'draft' => 'Draft',
            'waiting_verification' =>
                'Menunggu Verifikasi',
            'needs_revision' => 'Perlu Revisi',
            'approved' => 'Disetujui',
            'incomplete' => 'Belum Lengkap',
            'completed_offline' =>
                'Diselesaikan Offline',
            default => 'Belum Dibuat',
        };

        $reportStatusClass = match ($report?->status) {
            'waiting_verification' => 'warning',
            'needs_revision' => 'danger',
            'approved' => 'success',
            default => 'secondary',
        };
    @endphp

    {{-- Informasi jadwal --}}
    <div class="card border-0 shadow-sm mb-4">
        <div
            class="card-header bg-white py-3
                   d-flex justify-content-between
                   align-items-center flex-wrap gap-3"
        >
            <div>
                <h2 class="h5 fw-bold mb-1">
                    Jadwal WFH Aktif
                </h2>

                <small class="text-secondary">
                    {{
                        $membership
                            ->schedule
                            ->wfh_date
                            ->translatedFormat(
                                'l, d F Y'
                            )
                    }}
                </small>
            </div>

            <span
                class="badge text-bg-{{ $reportStatusClass }}"
            >
                {{ $reportStatusLabel }}
            </span>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <small class="text-secondary">
                        Jenis Jadwal
                    </small>

                    <div class="fw-semibold">
                        {{
                            $membership->is_schedule_change
                                ? 'Perubahan Jadwal'
                                : 'Jadwal Normal'
                        }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-secondary">
                        Batas Check-in
                    </small>

                    <div class="fw-semibold">
                        {{
                            $membership->checkin_deadline
                                ? $membership
                                    ->checkin_deadline
                                    ->format('H:i')
                                    . ' WIB'
                                : '-'
                        }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-secondary">
                        Status Laporan
                    </small>

                    <div class="fw-semibold">
                        {{ $reportStatusLabel }}
                    </div>
                </div>
            </div>

            {{-- Tombol sesuai tahapan Personel --}}
            <div class="d-flex flex-wrap gap-2">
                <a
                    href="{{ route('personnel.attendance.show') }}"
                    class="btn btn-outline-success"
                >
                    {{
                        $membership
                            ->attendance
                            ?->checkin_at
                                ? 'Lihat Presensi'
                                : 'Lakukan Check-in'
                    }}
                </a>

                @if ($membership->attendance?->checkin_at)
                    <a
                        href="{{
                            route(
                                'personnel.work-items.index'
                            )
                        }}"
                        class="btn btn-sikerja"
                    >
                        Pekerjaan
                    </a>

                    <a
                        href="{{
                            route(
                                'personnel.report.show'
                            )
                        }}"
                        class="btn btn-outline-primary"
                    >
                        Laporan & Check-out
                    </a>
                @endif

                <a
                    href="{{ route('notifications.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Notifikasi
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik pekerjaan --}}
    <div class="row g-3">
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Total Pekerjaan
                    </small>

                    <div class="h2 fw-bold mb-0">
                        {{ $workStatistics['total'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Tugas Pimpinan
                    </small>

                    <div class="h2 fw-bold mb-0">
                        {{ $workStatistics['leader_tasks'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Rencana Pribadi
                    </small>

                    <div class="h2 fw-bold mb-0">
                        {{ $workStatistics['personal_plans'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Belum Selesai
                    </small>

                    <div class="h2 fw-bold text-warning mb-0">
                        {{ $workStatistics['in_progress'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-secondary">
                        Selesai
                    </small>

                    <div class="h2 fw-bold text-success mb-0">
                        {{ $workStatistics['completed'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <h2 class="h5 fw-bold">
                Tidak Ada Jadwal WFH Aktif
            </h2>

            <p class="text-secondary mb-3">
                Jadwal WFH aktif akan ditampilkan
                pada halaman ini.
            </p>

            <a
                href="{{ route('notifications.index') }}"
                class="btn btn-outline-primary"
            >
                Lihat Notifikasi
            </a>
        </div>
    </div>
@endif
@endsection
