@extends('layouts.app')

@section('title', 'Laporan Kerja - SIKERJA')

@section('content')
<div
    class="d-flex flex-column flex-md-row
           justify-content-between align-items-md-center
           gap-3 mb-4"
>
    <div>
        <h1 class="h3 fw-bold mb-1">
            Laporan Kerja WFH
        </h1>

        <p class="text-secondary mb-0">
            {{
                $membership->schedule->wfh_date
                    ->translatedFormat('l, d F Y')
            }}
        </p>
    </div>

    <a
        href="{{ route('personnel.work-items.index') }}"
        class="btn btn-outline-secondary"
    >
        Kembali ke Pekerjaan
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Laporan belum dapat dikirim.</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Total Pekerjaan
                </small>

                <div class="display-6 fw-bold">
                    {{ $items->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Rencana Pribadi
                </small>

                <div class="display-6 fw-bold">
                    {{ $personalPlanCount }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Pekerjaan Selesai
                </small>

                <div class="display-6 fw-bold">
                    {{ $completedCount }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Status Laporan
                </small>

                <div class="h5 fw-bold mt-2">
                    {{
                        match ($report->status) {
                            'draft' => 'Draft',
                            'waiting_verification' =>
                                'Menunggu Verifikasi',
                            'approved' => 'Disetujui',
                            'needs_revision' => 'Perlu Revisi',
                            default => $report->status,
                        }
                    }}
                </div>
            </div>
        </div>
    </div>
</div>

@forelse ($items as $item)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div
                class="d-flex justify-content-between
                       align-items-start gap-3"
            >
                <div>
                    <span
                        class="badge {{
                            $item->source_type === 'leader_task'
                                ? 'text-bg-warning'
                                : 'text-bg-primary'
                        }}"
                    >
                        {{
                            $item->source_type === 'leader_task'
                                ? 'Tugas Pimpinan'
                                : 'Rencana Pribadi'
                        }}
                    </span>

                    <h2 class="h5 fw-bold mt-2">
                        {{ $item->title }}
                    </h2>
                </div>

                <span class="badge text-bg-light">
                    {{
                        match ($item->status) {
                            'not_started' => 'Belum Dimulai',
                            'in_progress' => 'Dikerjakan',
                            'blocked' => 'Terkendala',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            default => $item->status,
                        }
                    }}
                </span>
            </div>

            <hr>

            <div class="mb-3">
                <small class="text-secondary">
                    Progres/Hasil
                </small>

                <div>
                    {{ $item->progress ?: '-' }}
                </div>
            </div>

            <div class="mb-3">
                <small class="text-secondary">
                    Kendala
                </small>

                <div>
                    {{ $item->obstacle ?: '-' }}
                </div>
            </div>

            <div class="mb-3">
                <small class="text-secondary">
                    Tindak Lanjut
                </small>

                <div>
                    {{ $item->follow_up_plan ?: '-' }}
                </div>
            </div>

            <div>
                <small class="text-secondary">
                    Bukti PDF
                </small>

                <div>
                    {{ $item->files->count() }} file
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-warning">
        Belum ada pekerjaan dalam laporan.
    </div>
@endforelse

<div class="card border-0 shadow-sm mt-4">
    <div class="card-body">
        @if ($checkoutCompleted)
            <div class="alert alert-success mb-0">
                Check-out telah selesai dan laporan sudah dikunci.
            </div>
        @elseif ($report->status === 'waiting_verification')
            <div
                class="d-flex flex-column flex-md-row
                       justify-content-between
                       align-items-md-center gap-3"
            >
                <div>
                    <h2 class="h5 fw-bold mb-1">
                        Laporan Sudah Dikirim
                    </h2>

                    <p class="text-secondary mb-0">
                        Lanjutkan dengan foto dan lokasi check-out.
                    </p>
                </div>

                <a
                    href="{{ route('personnel.checkout.show') }}"
                    class="btn btn-success"
                >
                    Lakukan Check-out
                </a>
            </div>
        @elseif (! $report->is_locked)
            <form
                method="POST"
                action="{{ route('personnel.report.submit') }}"
                onsubmit="
                    return confirm(
                        'Kirim laporan kerja ini? Pastikan seluruh pekerjaan sudah lengkap.'
                    )
                "
            >
                @csrf

                <button
                    type="submit"
                    class="btn btn-sikerja btn-lg"
                >
                    Kirim Laporan Kerja
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
