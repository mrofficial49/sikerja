<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Dashboard SIKERJA</title>

    @vite('resources/js/app.js')
</head>

<body>
<nav class="navbar navbar-sikerja navbar-dark">
    <div class="container">
        <span class="navbar-brand fw-bold">
            SIKERJA
        </span>

        <form
            method="POST"
            action="{{ route('logout') }}"
            onsubmit="return confirm('Apakah Anda yakin ingin keluar?')"
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
</nav>

<main class="container py-5">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h3 fw-bold mb-4">
                Selamat Datang, {{ $user->name }}
            </h1>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-secondary">
                            ID Login
                        </small>

                        <div class="fw-semibold">
                            {{ $user->login_id }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-secondary">
                            Hak Akses
                        </small>

                        <div class="fw-semibold">
                            {{ $user->role?->name ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-secondary">
                            Jabatan
                        </small>

                        <div class="fw-semibold">
                            {{ $user->position }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-secondary">
                            Unit
                        </small>

                        <div class="fw-semibold">
                            {{ $user->unit?->name ?? 'Tidak terikat unit' }}
                        </div>
                    </div>
                </div>
            </div>

            @if ($user->must_change_password)
                <div class="alert alert-warning mt-4 mb-0">
                    Anda masih menggunakan password sementara.
                    Fitur wajib mengganti password akan dibuat
                    pada tahap berikutnya.
                </div>
            @endif
        </div>
    </div>
</main>
</body>
</html>
