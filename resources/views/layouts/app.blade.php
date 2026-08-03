<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>@yield('title', 'SIKERJA')</title>

    @vite('resources/js/app.js')
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-sikerja">
    <div class="container">
        <a
            class="navbar-brand fw-bold"
            href="{{ route('dashboard') }}"
        >
            SIKERJA
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavigation"
            aria-controls="mainNavigation"
            aria-expanded="false"
            aria-label="Buka navigasi"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div
            class="collapse navbar-collapse"
            id="mainNavigation"
        >
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a
                        class="nav-link
                        {{ request()->routeIs('*.dashboard')
                            ? 'active'
                            : '' }}"
                        href="{{ route('dashboard') }}"
                    >
                        Dashboard
                    </a>
                </li>

                {{-- Menu khusus Admin. --}}
                @if (auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a
                            class="nav-link
                            {{ request()->routeIs('admin.units.*')
                                ? 'active'
                                : '' }}"
                            href="{{ route('admin.units.index') }}"
                        >
                            Unit Kerja
                        </a>
                    </li>


                    <li class="nav-item">
                        <a
                            class="nav-link
                            {{ request()->routeIs('admin.users.*')
                                ? 'active'
                                : '' }}"
                            href="{{ route('admin.users.index') }}"
                        >
                            Pengguna
                        </a>
                    </li>


                    <li class="nav-item">
                        <a
                            class="nav-link
                            {{ request()->routeIs('admin.wfh-schedules.*')
                                ? 'active'
                                : '' }}"
                            href="{{ route('admin.wfh-schedules.index') }}"
                        >
                            Jadwal WFH
                        </a>
                    </li>
                @endif
                {{-- ======================================================
     MENU VERIFIKASI LAPORAN
     Hanya ditampilkan untuk Admin dan Pimpinan
====================================================== --}}

@if (auth()->check())

    {{-- Menu khusus Admin --}}
    {{-- ======================================================
     MENU KHUSUS ADMIN
====================================================== --}}
@if (
    auth()->check()
    && auth()->user()->role?->name === 'Admin'
)

    {{-- Menu untuk melihat rekap presensi dan laporan. --}}
    <li class="nav-item">
        <a
            href="{{ route('admin.monitoring.index') }}"
            class="nav-link
                {{ request()->routeIs('admin.monitoring.*')
                    ? 'active'
                    : '' }}"
        >
            Monitoring & Rekap
        </a>
    </li>

    {{-- Menu untuk memeriksa laporan Personel. --}}
    <li class="nav-item">
        <a
            href="{{ route('admin.reports.index') }}"
            class="nav-link
                {{ request()->routeIs('admin.reports.*')
                    ? 'active'
                    : '' }}"
        >
            Verifikasi Laporan
        </a>
    </li>

@endif

   {{-- ======================================================
     MENU KHUSUS PIMPINAN
====================================================== --}}
{{-- ======================================================
     MENU KHUSUS PIMPINAN
====================================================== --}}
@if (
    auth()->check()
    && auth()->user()->role?->name === 'Pimpinan'
)

    {{-- Menu untuk memberikan tugas kepada Personel. --}}
    <li class="nav-item">
        <a
            href="{{ route('leader.tasks.index') }}"
            class="nav-link
                {{ request()->routeIs('leader.tasks.*')
                    ? 'active'
                    : '' }}"
        >
            Tugas Personel
        </a>
    </li>

    {{-- Menu untuk melihat rekap presensi dan laporan. --}}
    <li class="nav-item">
        <a
            href="{{ route('leader.monitoring.index') }}"
            class="nav-link
                {{ request()->routeIs('leader.monitoring.*')
                    ? 'active'
                    : '' }}"
        >
            Monitoring & Rekap
        </a>
    </li>

    {{-- Menu untuk memeriksa laporan Personel. --}}
    <li class="nav-item">
        <a
            href="{{ route('leader.reports.index') }}"
            class="nav-link
                {{ request()->routeIs('leader.reports.*')
                    ? 'active'
                    : '' }}"
        >
            Verifikasi Laporan
        </a>
    </li>

@endif

@endif
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="text-white text-end d-none d-lg-block">
                    <div class="fw-semibold">
                        {{ auth()->user()->name }}
                    </div>

                    <small class="text-white-50">
                        {{ auth()->user()->role?->name ?? '-' }}
                    </small>
                </div>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    onsubmit="
                        return confirm(
                            'Apakah Anda yakin ingin keluar?'
                        )
                    "
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-outline-light btn-sm"
                    >
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    {{-- Pesan berhasil. --}}
    @if (session('success'))
        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Tutup"
            ></button>
        </div>
    @endif

    {{-- Pesan kesalahan umum. --}}
    @if (session('error'))
        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Tutup"
            ></button>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
