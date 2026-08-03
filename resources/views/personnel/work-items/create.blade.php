@extends('layouts.app')

@section('title', 'Tambah Rencana Kerja - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Tambah Rencana Kerja
    </h1>

    <p class="text-secondary mb-0">
        Masukkan pekerjaan yang akan dilaksanakan selama WFH.
    </p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form
            method="POST"
            action="{{ route('personnel.work-items.store') }}"
        >
            @csrf

            @include('personnel.work-items._form', [
                'workItem' => new \App\Models\WorkItem(),
                'submitLabel' => 'Simpan Rencana Kerja',
                'requiresChangeReason' =>
                    $requiresChangeReason,
            ])
        </form>
    </div>
</div>
@endsection
