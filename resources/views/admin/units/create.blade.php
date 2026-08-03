@extends('layouts.app')

@section('title', 'Tambah Unit - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Tambah Unit Kerja
    </h1>

    <p class="text-secondary mb-0">
        Masukkan data unit atau bagian baru.
    </p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form
            method="POST"
            action="{{ route('admin.units.store') }}"
        >
            @csrf

            @include('admin.units._form', [
                'unit' => new \App\Models\Unit(),
                'submitLabel' => 'Simpan Unit',
            ])
        </form>
    </div>
</div>
@endsection
