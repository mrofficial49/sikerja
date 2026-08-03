<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Ganti Password - SIKERJA</title>

    @vite('resources/js/app.js')
</head>

<body>
<div class="login-page d-flex align-items-center py-5">
    <div class="container">
        <div
            class="card border-0 shadow-lg mx-auto"
            style="max-width: 620px; border-radius: 20px;"
        >
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-4">
                    <div class="brand-logo mx-auto mb-3">
                        SK
                    </div>

                    <h1 class="h3 fw-bold">
                        Ganti Password
                    </h1>

                    <p class="text-secondary mb-0">
                        Halo, {{ $user->name }}.
                        Silakan buat password baru untuk akun Anda.
                    </p>
                </div>

                @if (session('warning'))
                    <div class="alert alert-warning">
                        {{ session('warning') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Password belum dapat disimpan.</strong>

                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-light border">
                    <strong>Syarat password baru:</strong>

                    <ul class="mb-0 mt-2">
                        <li>Minimal 8 karakter.</li>
                        <li>Memiliki huruf besar dan huruf kecil.</li>
                        <li>Memiliki minimal satu angka.</li>
                        <li>Memiliki minimal satu simbol.</li>
                    </ul>
                </div>

                <form
                    method="POST"
                    action="{{ route('password.update') }}"
                >
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label
                            for="current_password"
                            class="form-label fw-semibold"
                        >
                            Password Sementara/Lama
                        </label>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            class="form-control form-control-lg"
                            autocomplete="current-password"
                            required
                            autofocus
                        >
                    </div>

                    <div class="mb-3">
                        <label
                            for="password"
                            class="form-label fw-semibold"
                        >
                            Password Baru
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control form-control-lg"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label
                            for="password_confirmation"
                            class="form-label fw-semibold"
                        >
                            Ulangi Password Baru
                        </label>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control form-control-lg"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="btn btn-sikerja btn-lg w-100"
                    >
                        Simpan Password Baru
                    </button>
                </form>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="mt-3"
                    onsubmit="
                        return confirm(
                            'Apakah Anda yakin ingin keluar?'
                        )
                    "
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-outline-secondary w-100"
                    >
                        Keluar dari Sistem
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
