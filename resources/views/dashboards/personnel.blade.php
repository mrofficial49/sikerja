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

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Notifikasi Belum Dibaca
                </small>

                <div class="display-6 fw-bold mt-2">
                    {{ $unreadNotifications }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Status Jadwal WFH
                </small>

                <div class="h4 fw-bold mt-2 mb-0">
                    {{
                        $membership
                            ? 'Terjadwal'
                            : 'Tidak Ada Jadwal'
                    }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Status Check-in
                </small>

                <div class="h4 fw-bold mt-2 mb-0">
                    @if ($membership?->attendance?->checkin_at)
                        Sudah Check-in
                    @elseif ($membership)
                        Belum Check-in
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if ($membership)
    <div class="card border-0 shadow-sm mb-4">
        <div
            class="card-header bg-white py-3
                   d-flex justify-content-between
                   align-items-center flex-wrap gap-2"
        >
            <div>
                <h2 class="h5 fw-bold mb-1">
                    Jadwal WFH Aktif
                </h2>

                <small class="text-secondary">
                    {{
                        $membership->schedule->wfh_date
                            ->translatedFormat('l, d F Y')
                    }}
                </small>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a
                    href="{{ route('personnel.attendance.show') }}"
                    class="btn btn-outline-success"
                >
                    {{
                        $membership->attendance?->checkin_at
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
                        Rencana Kerja
                        ({{ $personalPlanCount }})
                    </a>


                    <a
                        href="{{ route('personnel.report.show') }}"
                        class="btn btn-outline-primary"
                    >
                        Laporan & Check-out
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
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
                                ? $membership->checkin_deadline
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
                        {{
                            $membership->workReport?->status
                                ?? 'Belum Dibuat'
                        }}
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

            <p class="text-secondary mb-0">
                Jadwal aktif akan ditampilkan pada halaman ini.
            </p>
        </div>
    </div>
@endif
@endsection
