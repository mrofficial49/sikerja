@extends('layouts.app')

@section('title', 'Dashboard Admin - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Dashboard Admin
    </h1>

    <p class="text-secondary mb-0">
        Selamat datang, {{ $user->name }}.
        Kelola pelaksanaan WFH melalui ringkasan berikut.
    </p>
</div>

{{-- Ringkasan data utama --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Total Pengguna
                </small>

                <div class="display-6 fw-bold">
                    {{ $statistics['users'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Total Unit
                </small>

                <div class="display-6 fw-bold">
                    {{ $statistics['units'] }}
                </div>
            </div>
        </div>
    </div>

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
                    Notifikasi Baru
                </small>

                <div class="display-6 fw-bold text-danger">
                    {{ $statistics['unread_notifications'] }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Ringkasan jadwal aktif --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <div
            class="d-flex justify-content-between
                   align-items-center flex-wrap gap-2"
        >
            <div>
                <h2 class="h5 fw-bold mb-1">
                    Pelaksanaan WFH Aktif
                </h2>

                <small class="text-secondary">
                    @if ($activeSchedule)
                        {{
                            $activeSchedule
                                ->wfh_date
                                ->translatedFormat('l, d F Y')
                        }}
                    @else
                        Tidak ada jadwal aktif
                    @endif
                </small>
            </div>

            <a
                href="{{ route('admin.monitoring.index') }}"
                class="btn btn-outline-primary"
            >
                Buka Monitoring
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <small class="text-secondary">
                        Sudah Check-in
                    </small>

                    <div class="h2 fw-bold text-success mb-0">
                        {{ $statistics['checked_in'] }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <small class="text-secondary">
                        Sudah Check-out
                    </small>

                    <div class="h2 fw-bold text-primary mb-0">
                        {{ $statistics['checked_out'] }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <small class="text-secondary">
                        Jadwal Aktif
                    </small>

                    <div class="h2 fw-bold mb-0">
                        {{ $statistics['active_schedules'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Status laporan --}}
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
                    Laporan Disetujui
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
                href="{{ route('admin.monitoring.index') }}"
                class="btn btn-primary"
            >
                Monitoring & Rekap
            </a>

            <a
                href="{{ route('admin.reports.index') }}"
                class="btn btn-outline-primary"
            >
                Verifikasi Laporan
            </a>

            <a
                href="{{ route('notifications.index') }}"
                class="btn btn-outline-secondary"
            >
                Notifikasi
            </a>

            @if (Route::has('admin.users.index'))
                <a
                    href="{{ route('admin.users.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Kelola Pengguna
                </a>
            @endif

            @if (Route::has('admin.units.index'))
                <a
                    href="{{ route('admin.units.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Kelola Unit
                </a>
            @endif

            @if (Route::has('admin.schedules.index'))
                <a
                    href="{{ route('admin.schedules.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Jadwal WFH
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
