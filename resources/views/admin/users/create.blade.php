@extends('layouts.app')

@section('title', 'Tambah Pengguna - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Tambah Pengguna
    </h1>

    <p class="text-secondary mb-0">
        Buat akun baru untuk Admin, Pimpinan, atau Personel.
    </p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form
            method="POST"
            action="{{ route('admin.users.store') }}"
        >
            @csrf

            @include('admin.users._form', [
                'user' => new \App\Models\User(),
                'isEdit' => false,
                'submitLabel' => 'Simpan Pengguna',
            ])
        </form>
    </div>
</div>
@endsection
