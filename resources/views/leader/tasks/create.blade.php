@extends('layouts.app')

@section('title', 'Berikan Tugas - SIKERJA')

@section('content')
<div class="container-fluid py-4">

    <div
        class="d-flex justify-content-between
               align-items-center gap-3 mb-4"
    >
        <div>
            <h1 class="h3 fw-bold mb-1">
                Berikan Tugas
            </h1>

            <p class="text-secondary mb-0">
                Berikan tugas kepada Personel pada jadwal WFH aktif.
            </p>
        </div>

        <a
            href="{{ route('leader.tasks.index') }}"
            class="btn btn-outline-secondary"
        >
            Kembali
        </a>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data berikut:</strong>

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form
                method="POST"
                action="{{ route('leader.tasks.store') }}"
            >
                @csrf

                <div class="mb-4">
                    <label
                        for="schedule_member_id"
                        class="form-label fw-semibold"
                    >
                        Personel Penerima Tugas
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        id="schedule_member_id"
                        name="schedule_member_id"
                        class="form-select"
                        required
                    >
                        <option value="">
                            Pilih Personel
                        </option>

                        @foreach ($members as $member)
                            <option
                                value="{{ $member->id }}"
                                @selected(
                                    old('schedule_member_id')
                                    == $member->id
                                )
                                @disabled(
                                    $member->attendance?->checkout_at
                                )
                            >
                                {{ $member->user?->name ?? '-' }}
                                —
                                {{ $member->user?->login_id ?? '-' }}
                                —
                                {{ $member->user?->unit?->code ?? '-' }}
                                —
                                {{
                                    $member->schedule?->wfh_date
                                        ?->format('d-m-Y')
                                    ?? '-'
                                }}

                                @if ($member->attendance?->checkout_at)
                                    — Sudah Check-out
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <div class="form-text">
                        Personel yang sudah check-out tidak dapat
                        menerima tugas baru.
                    </div>
                </div>

                <div class="mb-4">
                    <label
                        for="title"
                        class="form-label fw-semibold"
                    >
                        Judul Tugas
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        maxlength="255"
                        class="form-control"
                        placeholder="Contoh: Menyusun rekapitulasi kegiatan"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label
                        for="description"
                        class="form-label fw-semibold"
                    >
                        Uraian Tugas
                        <span class="text-danger">*</span>
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        maxlength="5000"
                        class="form-control"
                        placeholder="Jelaskan pekerjaan yang harus dilaksanakan"
                        required
                    >{{ old('description') }}</textarea>
                </div>

                <div class="mb-4">
                    <label
                        for="target_result"
                        class="form-label fw-semibold"
                    >
                        Target Hasil
                        <span class="text-danger">*</span>
                    </label>

                    <textarea
                        id="target_result"
                        name="target_result"
                        rows="4"
                        maxlength="3000"
                        class="form-control"
                        placeholder="Jelaskan hasil akhir yang diharapkan"
                        required
                    >{{ old('target_result') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Berikan Tugas
                    </button>

                    <a
                        href="{{ route('leader.tasks.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
