@extends('layouts.app')

@section('title', 'Dashboard Admin - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Dashboard Admin
    </h1>

    <p class="text-secondary mb-0">
        Selamat datang, {{ $user->name }}.
    </p>
</div>

<div class="row g-4">
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 fw-bold">Data Pengguna</h2>

                <p class="text-secondary">
                    Mengelola akun Admin, Pimpinan, dan Personel.
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
                <h2 class="h5 fw-bold">Jadwal WFH</h2>

                <p class="text-secondary">
                    Membuat jadwal dan menentukan personel WFH.
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
                <h2 class="h5 fw-bold">Rekap Laporan</h2>

                <p class="text-secondary">
                    Melihat ringkasan presensi dan laporan bulanan.
                </p>

                <span class="badge text-bg-secondary">
                    Akan dibuat
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
