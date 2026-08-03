<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Login - {{ $settings['app_name'] ?? 'SIKERJA' }}
    </title>

    {{-- Memuat Bootstrap dan CSS SIKERJA melalui Vite. --}}
    @vite('resources/js/app.js')
</head>

<body>
<div class="login-page d-flex align-items-center py-5">
    <div class="container">
        <div class="card login-card">
            <div class="row g-0">

                {{-- Bagian identitas aplikasi. --}}
                <div class="col-lg-6">
                    <div
                        class="login-brand-panel
                               h-100
                               d-flex
                               flex-column
                               justify-content-center
                               p-5"
                    >
                        <div class="brand-logo mb-4">
                            SK
                        </div>

                        <h1 class="fw-bold mb-3">
                            {{ $settings['app_name'] ?? 'SIKERJA' }}
                        </h1>

                        <p class="fs-5 text-white-50 mb-4">
                            {{
                                $settings['app_subtitle']
                                ?? 'Sistem Informasi Kinerja dan Aktivitas Personel'
                            }}
                        </p>

                        <div class="border-top border-light pt-4">
                            <small class="text-white-50">
                                Monitoring WFH, presensi, aktivitas,
                                tugas, dan laporan personel.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Bagian formulir login. --}}
                <div class="col-lg-6 bg-white">
                    <div
                        class="h-100
                               d-flex
                               flex-column
                               justify-content-center
                               p-4
                               p-lg-5"
                    >
                        <h2 class="fw-bold text-dark mb-2">
                            Masuk ke Sistem
                        </h2>

                        <p class="text-secondary mb-4">
                            Gunakan NRP, NIP, atau ID Login Anda.
                        </p>

                        {{-- Pesan setelah logout. --}}
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- Pesan kesalahan validasi. --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('login.process') }}"
                        >
                            {{-- Token keamanan wajib Laravel. --}}
                            @csrf

                            <div class="mb-3">
                                <label
                                    for="login_id"
                                    class="form-label fw-semibold"
                                >
                                    ID Login
                                </label>

                                <input
                                    type="text"
                                    id="login_id"
                                    name="login_id"
                                    value="{{ old('login_id') }}"
                                    class="form-control form-control-lg"
                                    placeholder="Masukkan NRP, NIP, atau ID"
                                    maxlength="50"
                                    autocomplete="username"
                                    required
                                    autofocus
                                >
                            </div>

                            <div class="mb-4">
                                <label
                                    for="password"
                                    class="form-label fw-semibold"
                                >
                                    Password
                                </label>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control form-control-lg"
                                    placeholder="Masukkan password"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>

                            <button
                                type="submit"
                                class="btn btn-sikerja btn-lg w-100"
                            >
                                Masuk
                            </button>
                        </form>

                        <div class="mt-4 text-center text-secondary">
                            <small>
                                Kendala login? Hubungi
                                <strong>
                                    {{
                                        $settings['admin_contact_name']
                                        ?? 'Admin SIKERJA'
                                    }}
                                </strong>

                                <br>

                                {{
                                    $settings['admin_contact_phone']
                                    ?? '08xxxxxxxxxx'
                                }}
                            </small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>
