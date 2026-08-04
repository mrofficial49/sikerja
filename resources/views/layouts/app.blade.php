<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta name="theme-color" content="#173b2b">

    <title>@yield('title', 'SIKERJA')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="sikerja-app-body">
@php
    /*
     * Data pengguna yang sedang login.
     */
    $authenticatedUser = auth()->user();

    /*
     * Jumlah notifikasi yang belum dibaca.
     */
    $navbarUnreadNotificationCount = $authenticatedUser
        ->appNotifications()
        ->unread()
        ->count();
@endphp

<div class="sikerja-app-shell">

    {{-- =====================================================
         SIDEBAR DESKTOP
    ====================================================== --}}
    <aside class="sikerja-sidebar d-none d-lg-flex">
        <div class="sikerja-sidebar-brand">
            <a
                href="{{ route('dashboard') }}"
                class="sikerja-brand-link"
            >
               {{-- Logo SIKERJA versi mobile --}}
<span class="sikerja-brand-mark">
    <img
        src="{{ asset('images/logo-sikerja.png') }}"
        alt="Logo SIKERJA"
        class="sikerja-brand-image"
    >
</span>

                <span>
                    <strong>SIKERJA</strong>

                    <small>
                        Sistem Kinerja Personel
                    </small>
                </span>
            </a>
        </div>

        <div class="sikerja-sidebar-section-title">
            Menu Utama
        </div>

        <div class="sikerja-sidebar-navigation">
            @include(
                'layouts.partials.sidebar-menu'
            )
        </div>

        <div class="sikerja-sidebar-footer">
            <div class="sikerja-user-card">
                <div class="sikerja-user-avatar">
                    {{
                        strtoupper(
                            substr(
                                $authenticatedUser->name,
                                0,
                                1
                            )
                        )
                    }}
                </div>

                <div class="sikerja-user-information">
                    <strong>
                        {{ $authenticatedUser->name }}
                    </strong>

                    <small>
                        {{
                            $authenticatedUser->role?->name
                            ?? '-'
                        }}
                    </small>
                </div>
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
                    class="sikerja-logout-button"
                >
                    Keluar dari Sistem
                </button>
            </form>
        </div>
    </aside>

    {{-- =====================================================
         AREA UTAMA
    ====================================================== --}}
    <div class="sikerja-main">

        {{-- Topbar --}}
        <header class="sikerja-topbar">
            <div class="d-flex align-items-center gap-3">
                <button
                    type="button"
                    class="sikerja-mobile-menu-button
                           d-lg-none"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#mobileSidebar"
                    aria-controls="mobileSidebar"
                    aria-label="Buka menu"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div>
                    <small class="sikerja-topbar-eyebrow">
                        Sistem Informasi Kinerja
                    </small>

                    <div class="sikerja-topbar-title">
                        @yield('title', 'SIKERJA')
                    </div>
                </div>
            </div>

            <div class="sikerja-topbar-actions">
                <a
                    href="{{ route('notifications.index') }}"
                    class="sikerja-notification-button"
                    aria-label="Notifikasi"
                >
                    <span class="notification-symbol">
                        ●
                    </span>

                    @if (
                        $navbarUnreadNotificationCount > 0
                    )
                        <span
                            class="sikerja-notification-count"
                        >
                            {{
                                $navbarUnreadNotificationCount
                                    > 99
                                    ? '99+'
                                    : $navbarUnreadNotificationCount
                            }}
                        </span>
                    @endif
                </a>

                <div
                    class="sikerja-topbar-profile
                           d-none d-sm-flex"
                >
                    <div class="sikerja-profile-avatar">
                        {{
                            strtoupper(
                                substr(
                                    $authenticatedUser->name,
                                    0,
                                    1
                                )
                            )
                        }}
                    </div>

                    <div>
                        <strong>
                            {{ $authenticatedUser->name }}
                        </strong>

                        <small>
                            {{
                                $authenticatedUser
                                    ->unit
                                    ?->name
                                ?? $authenticatedUser
                                    ->role
                                    ?->name
                                ?? '-'
                            }}
                        </small>
                    </div>
                </div>
            </div>
        </header>

        {{-- Konten --}}
        <main class="sikerja-content">
            <div class="sikerja-content-inner">

                {{-- Pesan berhasil --}}
                @if (session('success'))
                    <div
                        class="alert alert-success
                               alert-dismissible fade show
                               sikerja-alert"
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

                {{-- Pesan kesalahan --}}
                @if (session('error'))
                    <div
                        class="alert alert-danger
                               alert-dismissible fade show
                               sikerja-alert"
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

                {{-- Pesan peringatan --}}
                @if (session('warning'))
                    <div
                        class="alert alert-warning
                               alert-dismissible fade show
                               sikerja-alert"
                        role="alert"
                    >
                        {{ session('warning') }}

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Tutup"
                        ></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

{{-- =====================================================
     SIDEBAR MOBILE
====================================================== --}}
<div
    class="offcanvas offcanvas-start
           sikerja-mobile-sidebar"
    tabindex="-1"
    id="mobileSidebar"
    aria-labelledby="mobileSidebarLabel"
>
    <div class="offcanvas-header">
        <div
            id="mobileSidebarLabel"
            class="sikerja-brand-link"
        >
            <span class="sikerja-brand-mark">
                S
            </span>

            <span>
                <strong>SIKERJA</strong>
                <small>Sistem Kinerja Personel</small>
            </span>
        </div>

        <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="offcanvas"
            aria-label="Tutup"
        ></button>
    </div>

    <div class="offcanvas-body">
        <div class="sikerja-sidebar-section-title">
            Menu Utama
        </div>

        @include(
            'layouts.partials.sidebar-menu'
        )

        <div class="sikerja-mobile-user mt-auto">
            <div class="sikerja-user-card">
                <div class="sikerja-user-avatar">
                    {{
                        strtoupper(
                            substr(
                                $authenticatedUser->name,
                                0,
                                1
                            )
                        )
                    }}
                </div>

                <div class="sikerja-user-information">
                    <strong>
                        {{ $authenticatedUser->name }}
                    </strong>

                    <small>
                        {{
                            $authenticatedUser->role?->name
                            ?? '-'
                        }}
                    </small>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="mt-3"
            >
                @csrf

                <button
                    type="submit"
                    class="sikerja-logout-button"
                >
                    Keluar dari Sistem
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
