@extends('layouts.app')

@section('title', 'Dashboard Pimpinan - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Dashboard Pimpinan
    </h1>

    <p class="text-secondary mb-0">
        Selamat datang, {{ $user->name }}.
        Pantau Personel, tugas, dan laporan WFH.
    </p>
</div>

{{-- Statistik utama --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Personel Terjadwal
                </small>

                <div class="display-6 fw-bold">
                    {{ $statistics['scheduled_personnel'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Sudah Check-in
                </small>

                <div class="display-6 fw-bold text-success">
                    {{ $statistics['checked_in'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Sudah Check-out
                </small>

                <div class="display-6 fw-bold text-primary">
                    {{ $statistics['checked_out'] }}
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
                    {{ $statistics['unread_notifications'] }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Informasi jadwal aktif --}}
<div class="alert alert-light border mb-4">
    <strong>Jadwal WFH aktif:</strong>

    @if ($activeSchedule)
        {{
            $activeSchedule
                ->wfh_date
                ->translatedFormat('l, d F Y')
        }}
    @else
        Tidak ada jadwal aktif.
    @endif
</div>

{{-- Statistik tugas --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-primary shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Total Tugas Diberikan
                </small>

                <div class="h2 fw-bold mb-0">
                    {{ $statistics['leader_tasks'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-warning shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Tugas Belum Selesai
                </small>

                <div class="h2 fw-bold mb-0">
                    {{ $statistics['active_tasks'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-success shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Tugas Selesai
                </small>

                <div class="h2 fw-bold mb-0">
                    {{ $statistics['completed_tasks'] }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Statistik laporan --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-warning shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Menunggu Verifikasi
                </small>

                <div class="h2 fw-bold mb-0">
                    {{ $statistics['waiting_reports'] }}
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
                    {{ $statistics['revision_reports'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-success shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Disetujui
                </small>

                <div class="h2 fw-bold mb-0">
                    {{ $statistics['approved_reports'] }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Akses cepat --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h2 class="h5 fw-bold mb-0">
            Akses Cepat
        </h2>
    </div>

    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <a
                href="{{ route('leader.tasks.create') }}"
                class="btn btn-primary"
            >
                Berikan Tugas
            </a>

            <a
                href="{{ route('leader.tasks.index') }}"
                class="btn btn-outline-primary"
            >
                Daftar Tugas
            </a>

            <a
                href="{{ route('leader.monitoring.index') }}"
                class="btn btn-outline-primary"
            >
                Monitoring & Rekap
            </a>

            <a
                href="{{ route('leader.reports.index') }}"
                class="btn btn-outline-success"
            >
                Verifikasi Laporan
            </a>

            <a
                href="{{ route('notifications.index') }}"
                class="btn btn-outline-secondary"
            >
                Notifikasi
            </a>
        </div>
    </div>
</div>
@endsection
