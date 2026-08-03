@extends('layouts.app')

@section('title', 'Dashboard Pimpinan - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Dashboard Pimpinan
    </h1>

    <p class="text-secondary mb-0">
        Selamat datang, {{ $user->name }}.
    </p>
</div>

<div class="row g-4">
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 fw-bold">Monitoring Personel</h2>

                <p class="text-secondary">
                    Memantau kehadiran dan aktivitas personel WFH.
                </p>

                <span class="badge text-bg-secondary">
                    Akan dibuat
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 fw-bold">Tugas Personel</h2>

                <p class="text-secondary">
                    Memberikan dan memantau tugas kepada personel.
                </p>

                <span class="badge text-bg-secondary">
                    Akan dibuat
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 fw-bold">Verifikasi Laporan</h2>

                <p class="text-secondary">
                    Memeriksa, menyetujui, atau meminta revisi laporan.
                </p>

                <span class="badge text-bg-secondary">
                    Akan dibuat
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
