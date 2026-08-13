@extends('layouts.app')

@section('title', 'Rencana Kerja - SIKERJA')

@section('content')
<div
    class="d-flex flex-column flex-md-row
           justify-content-between align-items-md-center
           gap-3 mb-4"
>
    <div>
        <h1 class="h3 fw-bold mb-1">
            Rencana Kerja
        </h1>

        <p class="text-secondary mb-0">
            {{
                $membership->schedule->wfh_date
                    ->translatedFormat('l, d F Y')
            }}
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a
            href="{{ route('personnel.report.show') }}"
            class="btn btn-outline-success"
        >
            Ringkasan & Kirim Laporan
        </a>

        @if ($canModify)
            <a
                href="{{ route('personnel.work-items.create') }}"
                class="btn btn-sikerja"
            >
                + Tambah Rencana Kerja
            </a>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Rencana Pribadi
                </small>

                <div class="display-6 fw-bold mt-2">
                    {{ $personalPlans->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Tugas Pimpinan
                </small>

                <div class="display-6 fw-bold mt-2">
                    {{ $leaderTasks->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-secondary">
                    Status Laporan
                </small>

                <div class="h5 fw-bold mt-2 mb-0">
                    {{
                        match ($report->status) {
                            'draft' => 'Draft',
                            'waiting_verification' =>
                                'Menunggu Verifikasi',
                            'approved' => 'Disetujui',
                            'needs_revision' => 'Perlu Revisi',
                            'incomplete' => 'Tidak Lengkap',
                            'completed_offline' =>
                                'Selesai Offline',
                            default => $report->status,
                        }
                    }}
                </div>
            </div>
        </div>
    </div>
</div>

@if ($personalPlans->isEmpty())
    <div class="alert alert-warning">
        <strong>Rencana kerja belum dibuat.</strong>
        Anda wajib membuat minimal satu rencana kerja pribadi
        sebelum dapat mengirim laporan dan melakukan check-out.
    </div>
@endif

@if (! $canModify)
    <div class="alert alert-secondary">
        Rencana kerja sudah dikunci dan tidak dapat diubah
        setelah check-out.
    </div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h2 class="h5 fw-bold mb-0">
            Rencana Kerja Pribadi
        </h2>
    </div>

    <div class="card-body">
        @forelse ($personalPlans as $plan)
            <div
                class="border rounded p-4
                       {{ ! $loop->last ? 'mb-3' : '' }}"
            >
                <div
                    class="d-flex flex-column flex-lg-row
                           justify-content-between gap-3"
                >
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span class="badge text-bg-primary">
                                Rencana Pribadi
                            </span>

                            <span class="badge text-bg-light">
                                {{
                                    match ($plan->status) {
                                        'not_started' => 'Belum Dimulai',
                                        'in_progress' => 'Sedang Dikerjakan',
                                        'blocked' => 'Terkendala',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => $plan->status,
                                    }
                                }}
                            </span>
                        </div>

                        <h3 class="h5 fw-bold">
                            {{ $plan->title }}
                        </h3>

                        <div class="mb-3">
                            <small class="text-secondary">
                                Uraian Pekerjaan
                            </small>

                            <div>
                                {{ $plan->description }}
                            </div>
                        </div>

                        <div>
                            <small class="text-secondary">
                                Target Hasil
                            </small>

                            <div>
                                {{ $plan->target_result }}
                            </div>
                        </div>


                        <a
                            href="{{
                                route(
                                    'personnel.work-execution.edit',
                                    $plan
                                )
                            }}"
                            class="btn btn-sm
                                   btn-outline-success mt-3"
                        >
                            Pelaksanaan Pekerjaan
                        </a>
                    </div>

                    @if (
    $canModify
    && ! in_array(
        $plan->status,
        [
            'completed',
            'cancelled',
        ],
        true
    )
)
                        <div
                            class="d-flex flex-row flex-lg-column
                                   align-items-start gap-2"
                        >
                            <a
                                href="{{
                                    route(
                                        'personnel.work-items.edit',
                                        $plan
                                    )
                                }}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                Edit
                            </a>

                            <form
                                method="POST"
                                action="{{
                                    route(
                                        'personnel.work-items.destroy',
                                        $plan
                                    )
                                }}"
                                onsubmit="
                                    return confirmDeletePlan(
                                        this,
                                        {{
                                            $requiresChangeReason
                                                ? 'true'
                                                : 'false'
                                        }}
                                    )
                                "
                            >
                                @csrf
                                @method('DELETE')

                                <input
                                    type="hidden"
                                    name="change_reason"
                                    value=""
                                >

                                <button
                                    type="submit"
                                    class="btn btn-sm
                                           btn-outline-danger"
                                >
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-secondary py-5">
                Belum ada rencana kerja pribadi.
            </div>
        @endforelse
    </div>
</div>

@if ($leaderTasks->isNotEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h2 class="h5 fw-bold mb-0">
                Tugas dari Pimpinan
            </h2>
        </div>

        <div class="card-body">
            @foreach ($leaderTasks as $task)
                <div
                    class="border rounded p-4
                           {{ ! $loop->last ? 'mb-3' : '' }}"
                >
                    <span class="badge text-bg-warning mb-2">
                        Tugas Pimpinan
                    </span>

                    <h3 class="h5 fw-bold">
                        {{ $task->title }}
                    </h3>

                    <p class="mb-2">
                        {{ $task->description }}
                    </p>

                    <small class="text-secondary">
                        Target: {{ $task->target_result }}
                    </small>


                    <div class="mt-3">
                        <a
                            href="{{
                                route(
                                    'personnel.work-execution.edit',
                                    $task
                                )
                            }}"
                            class="btn btn-sm
                                   btn-outline-success"
                        >
                            Pelaksanaan Pekerjaan
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<script>
function confirmDeletePlan(form, requiresReason) {
    if (requiresReason) {
        const reason = window.prompt(
            'Masukkan alasan perubahan laporan:'
        );

        if (
            reason === null
            || reason.trim() === ''
        ) {
            return false;
        }

        form.querySelector(
            'input[name="change_reason"]'
        ).value = reason.trim();
    }

    return window.confirm(
        'Hapus rencana kerja ini?'
    );
}
</script>
@endsection
