@extends('layouts.app')

@section('title', 'Pelaksanaan Pekerjaan - SIKERJA')

@section('content')
<div
    class="d-flex flex-column flex-md-row
           justify-content-between align-items-md-center
           gap-3 mb-4"
>
    <div>
        <h1 class="h3 fw-bold mb-1">
            Pelaksanaan Pekerjaan
        </h1>

        <p class="text-secondary mb-0">
            Isi progres, kendala, tindak lanjut, dan bukti PDF.
        </p>
    </div>

    <a
        href="{{ route('personnel.work-items.index') }}"
        class="btn btn-outline-secondary"
    >
        Kembali
    </a>
</div>

@if (! $canModify)
    <div class="alert alert-secondary">
        Laporan telah dikunci atau Personel sudah melakukan
        check-out. Data hanya dapat dilihat.
    </div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap gap-2 mb-2">
            <span
                class="badge {{
                    $workItem->source_type === 'leader_task'
                        ? 'text-bg-warning'
                        : 'text-bg-primary'
                }}"
            >
                {{
                    $workItem->source_type === 'leader_task'
                        ? 'Tugas Pimpinan'
                        : 'Rencana Pribadi'
                }}
            </span>
        </div>

        <h2 class="h5 fw-bold mb-0">
            {{ $workItem->title }}
        </h2>
    </div>

    <div class="card-body">
        <div class="mb-3">
            <small class="text-secondary">
                Uraian Pekerjaan
            </small>

            <div>
                {{ $workItem->description }}
            </div>
        </div>

        <div>
            <small class="text-secondary">
                Target Hasil
            </small>

            <div>
                {{ $workItem->target_result }}
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h2 class="h5 fw-bold mb-0">
            Status Pelaksanaan
        </h2>
    </div>

    <div class="card-body p-4">
        <form
            method="POST"
            action="{{
                route(
                    'personnel.work-execution.update',
                    $workItem
                )
            }}"
        >
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label
                    for="status"
                    class="form-label fw-semibold"
                >
                    Status Pekerjaan
                    <span class="text-danger">*</span>
                </label>

                <select
                    id="status"
                    name="status"
                    class="form-select
                        @error('status') is-invalid @enderror"
                    required
                    @disabled(! $canModify)
                >
                    <option
                        value="not_started"
                        @selected(
                            old('status', $workItem->status)
                            === 'not_started'
                        )
                    >
                        Belum Dimulai
                    </option>

                    <option
                        value="in_progress"
                        @selected(
                            old('status', $workItem->status)
                            === 'in_progress'
                        )
                    >
                        Sedang Dikerjakan
                    </option>

                    <option
                        value="blocked"
                        @selected(
                            old('status', $workItem->status)
                            === 'blocked'
                        )
                    >
                        Terkendala
                    </option>

                    <option
                        value="completed"
                        @selected(
                            old('status', $workItem->status)
                            === 'completed'
                        )
                    >
                        Selesai
                    </option>

                    <option
                        value="cancelled"
                        @selected(
                            old('status', $workItem->status)
                            === 'cancelled'
                        )
                    >
                        Dibatalkan
                    </option>
                </select>

                @error('status')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label
                    for="progress"
                    class="form-label fw-semibold"
                >
                    Progres/Hasil Pelaksanaan
                </label>

                <textarea
                    id="progress"
                    name="progress"
                    rows="5"
                    maxlength="5000"
                    class="form-control
                        @error('progress') is-invalid @enderror"
                    placeholder="Jelaskan pekerjaan yang sudah dilaksanakan dan hasilnya"
                    @disabled(! $canModify)
                >{{ old('progress', $workItem->progress) }}</textarea>

                @error('progress')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label
                    for="obstacle"
                    class="form-label fw-semibold"
                >
                    Kendala
                </label>

                <textarea
                    id="obstacle"
                    name="obstacle"
                    rows="4"
                    maxlength="5000"
                    class="form-control
                        @error('obstacle') is-invalid @enderror"
                    placeholder="Jelaskan kendala yang dihadapi, bila ada"
                    @disabled(! $canModify)
                >{{ old('obstacle', $workItem->obstacle) }}</textarea>

                @error('obstacle')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label
                    for="follow_up_plan"
                    class="form-label fw-semibold"
                >
                    Rencana Tindak Lanjut
                </label>

                <textarea
                    id="follow_up_plan"
                    name="follow_up_plan"
                    rows="4"
                    maxlength="5000"
                    class="form-control
                        @error('follow_up_plan')
                            is-invalid
                        @enderror"
                    placeholder="Jelaskan pekerjaan lanjutan yang diperlukan"
                    @disabled(! $canModify)
                >{{ old('follow_up_plan', $workItem->follow_up_plan) }}</textarea>

                @error('follow_up_plan')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-check mb-4">
                <input
                    type="checkbox"
                    id="continue_offline"
                    name="continue_offline"
                    value="1"
                    class="form-check-input"
                    @checked(
                        old(
                            'continue_offline',
                            $workItem->continue_offline
                        )
                    )
                    @disabled(! $canModify)
                >

                <label
                    for="continue_offline"
                    class="form-check-label"
                >
                    Pekerjaan akan dilanjutkan secara offline
                    setelah jam WFH
                </label>
            </div>

            @if ($requiresChangeReason)
                <div class="mb-4">
                    <label
                        for="execution_change_reason"
                        class="form-label fw-semibold"
                    >
                        Alasan Perubahan
                        <span class="text-danger">*</span>
                    </label>

                    <textarea
                        id="execution_change_reason"
                        name="change_reason"
                        rows="3"
                        maxlength="1000"
                        class="form-control
                            @error('change_reason')
                                is-invalid
                            @enderror"
                        required
                        @disabled(! $canModify)
                    >{{ old('change_reason') }}</textarea>

                    <div class="form-text text-danger">
                        Laporan sudah pernah dikirim. Perubahan akan
                        mengembalikan laporan menjadi draft.
                    </div>

                    @error('change_reason')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            @endif

            @if ($canModify)
                <button
                    type="submit"
                    class="btn btn-sikerja"
                >
                    Simpan Pelaksanaan
                </button>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h2 class="h5 fw-bold mb-1">
            Bukti Pekerjaan
        </h2>

        <small class="text-secondary">
            Format PDF dengan ukuran maksimal 10 MB.
        </small>
    </div>

    <div class="card-body">
        @if ($canModify)
            <form
                method="POST"
                action="{{
                    route(
                        'personnel.work-execution.files.store',
                        $workItem
                    )
                }}"
                enctype="multipart/form-data"
                class="border rounded p-3 mb-4"
            >
                @csrf

                <div class="mb-3">
                    <label
                        for="file"
                        class="form-label fw-semibold"
                    >
                        Pilih PDF
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="file"
                        id="file"
                        name="file"
                        class="form-control
                            @error('file') is-invalid @enderror"
                        accept=".pdf,application/pdf"
                        required
                    >

                    @error('file')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label
                        for="description"
                        class="form-label fw-semibold"
                    >
                        Keterangan File
                    </label>

                    <input
                        type="text"
                        id="description"
                        name="description"
                        value="{{ old('description') }}"
                        class="form-control
                            @error('description')
                                is-invalid
                            @enderror"
                        maxlength="1000"
                        placeholder="Contoh: Laporan hasil penyusunan dokumen"
                    >

                    @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                @if ($requiresChangeReason)
                    <div class="mb-3">
                        <label
                            for="file_change_reason"
                            class="form-label fw-semibold"
                        >
                            Alasan Penambahan File
                            <span class="text-danger">*</span>
                        </label>

                        <textarea
                            id="file_change_reason"
                            name="change_reason"
                            rows="3"
                            maxlength="1000"
                            class="form-control"
                            required
                        ></textarea>
                    </div>
                @endif

                <button
                    type="submit"
                    class="btn btn-outline-success"
                >
                    Unggah Bukti PDF
                </button>
            </form>
        @endif

        @forelse ($files as $file)
            <div
                class="border rounded p-3
                       {{ ! $loop->last ? 'mb-3' : '' }}"
            >
                <div
                    class="d-flex flex-column flex-md-row
                           justify-content-between gap-3"
                >
                    <div>
                        <div class="fw-semibold">
                            {{ $file->original_name }}
                        </div>

                        <small class="text-secondary">
                            {{
                                number_format(
                                    $file->file_size / 1024,
                                    1,
                                    ',',
                                    '.'
                                )
                            }}
                            KB

                            · Diunggah:
                            {{
                                $file->uploaded_at
                                    ? $file->uploaded_at
                                        ->format('d-m-Y H:i')
                                    : '-'
                            }}
                        </small>

                        @if ($file->description)
                            <div class="mt-2">
                                {{ $file->description }}
                            </div>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a
                            href="{{
                                route(
                                    'personnel.work-execution.files.download',
                                    [
                                        $workItem,
                                        $file,
                                    ]
                                )
                            }}"
                            class="btn btn-sm btn-outline-primary"
                        >
                            Unduh
                        </a>

                        @if ($canModify)
                            <form
                                method="POST"
                                action="{{
                                    route(
                                        'personnel.work-execution.files.destroy',
                                        [
                                            $workItem,
                                            $file,
                                        ]
                                    )
                                }}"
                                onsubmit="
                                    return confirmDeleteFile(
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
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-secondary py-4">
                Belum ada bukti pekerjaan yang diunggah.
            </div>
        @endforelse
    </div>
</div>

<script>
function confirmDeleteFile(form, requiresReason) {
    if (requiresReason) {
        const reason = window.prompt(
            'Masukkan alasan penghapusan file:'
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
        'Hapus bukti PDF ini?'
    );
}

document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('file');

    /*
     * Form upload tidak selalu tampil, misalnya setelah
     * laporan dikunci. Karena itu kita periksa dahulu.
     */
    if (! fileInput) {
        return;
    }

    fileInput.addEventListener('change', function () {
        const selectedFile = this.files[0];

        if (! selectedFile) {
            return;
        }

        /*
         * Batas aplikasi adalah 10 MB.
         * 1 MB = 1.024 × 1.024 byte.
         */
        const maximumSize = 10 * 1024 * 1024;

        if (selectedFile.size > maximumSize) {
            alert(
                'Ukuran PDF terlalu besar. Maksimal 10 MB.'
            );

            /*
             * Kosongkan pilihan agar file tidak dapat dikirim.
             */
            this.value = '';
        }
    });
});
</script>
@endsection
