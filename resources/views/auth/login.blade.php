<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta name="theme-color" content="#10291e">

    <title>
        Login - {{ $settings['app_name'] ?? 'SIKERJA' }}
    </title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body>
<div class="login-page">
    <div class="login-shell">
        <div class="row g-0">

            {{-- ==================================================
                 PANEL IDENTITAS
            =================================================== --}}
            <section
                class="col-lg-6 login-brand-panel
                       d-flex align-items-center p-5"
            >
                <div class="login-brand-content">
                   {{-- Logo SIKERJA di halaman login --}}
<div class="login-logo mb-4">
    <img
        src="{{ asset('images/logo-sikerja.png') }}"
        alt="Logo SIKERJA"
        class="login-logo-image"
    >
</div>

                    <!-- <div
                        class="text-uppercase mb-3"
                        style="
                            color: #d0aa55;
                            font-size: 11px;
                            font-weight: 800;
                            letter-spacing: .18em;
                        "
                    >
                        Sistem Informasi Kinerja
                    </div> -->

                    <h1
                        class="display-5 fw-bold text-white mb-3"
                    >
                        {{
                            $settings['app_name']
                            ?? 'SIKERJA'
                        }}
                    </h1>

                    <p
                        class="fs-5 mb-0"
                        style="
                            color: rgba(255,255,255,.68);
                            line-height: 1.7;
                        "
                    >
                        {{
                            $settings['app_subtitle']
                            ?? 'Sistem Informasi Kinerja dan Aktivitas Personel'
                        }}
                    </p>

                    <div class="login-feature-list">
                        <div class="login-feature-item">
                            <span>01</span>
                            Presensi WFH berbasis foto dan GPS
                        </div>

                        <div class="login-feature-item">
                            <span>02</span>
                            Monitoring pekerjaan secara terintegrasi
                        </div>

                        <div class="login-feature-item">
                            <span>03</span>
                            Pelaporan dan verifikasi berjenjang
                        </div>

                        <div class="login-feature-item">
                            <span>04</span>
                            Data tersimpan aman dan terdokumentasi
                        </div>
                    </div>

                    <div
                        class="mt-5 pt-4"
                        style="
                            border-top:
                                1px solid rgba(255,255,255,.11);
                            color: rgba(255,255,255,.42);
                            font-size: 11px;
                        "
                    >
                        Profesional · Responsif · Integratif · Modern · Adaptif 
                    </div>
                </div>
            </section>

            {{-- ==================================================
                 FORM LOGIN
            =================================================== --}}
            <section
                class="col-lg-6 login-form-panel
                       p-4 p-md-5"
            >
                <div class="login-form-wrapper">
                    <div class="login-form-heading">
                        <div
                            class="text-uppercase mb-2"
                            style="
                                color: #2d6349;
                                font-size: 10px;
                                font-weight: 800;
                                letter-spacing: .16em;
                            "
                        >
                            Selamat Datang
                        </div>

                        <h2>
                            Masuk ke Sistem
                        </h2>

                        <p class="text-secondary mb-0">
                            Gunakan NRP, NIP, atau ID Login
                            yang telah terdaftar.
                        </p>
                    </div>

                    @if (session('success'))
                        <div
                            class="alert alert-success
                                   sikerja-alert"
                        >
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            class="alert alert-danger
                                   sikerja-alert"
                        >
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('login.process') }}"
                    >
                        @csrf

                        <div class="mb-4">
                            <label
                                for="login_id"
                                class="form-label fw-bold"
                            >
                                ID Login
                            </label>

                            <input
                                type="text"
                                id="login_id"
                                name="login_id"
                                value="{{ old('login_id') }}"
                                class="form-control
                                       form-control-lg"
                                placeholder="NRP, NIP, atau ID Login"
                                maxlength="50"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mb-4">
                            <label
                                for="password"
                                class="form-label fw-bold"
                            >
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control
                                       form-control-lg"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <button
                            type="submit"
                            class="btn btn-sikerja
                                   btn-lg w-100
                                   login-submit-button"
                        >
                            Masuk ke SIKERJA
                        </button>
                    </form>

                    <div
                        class="mt-4 pt-4 text-center"
                        style="
                            border-top: 1px solid #e4ebe6;
                            color: #748078;
                            font-size: 12px;
                        "
                    >
                        Kendala akses? Hubungi

                        <strong style="color: #173b2b;">
                            {{
                                $settings['admin_contact_name']
                                ?? 'Admin SIKERJA'
                            }}
                        </strong>

                        <div class="mt-1">
                            {{
                                $settings['admin_contact_phone']
                                ?? '08xxxxxxxxxx'
                            }}
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>
</body>
</html>
