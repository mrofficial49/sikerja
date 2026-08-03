@extends('layouts.app')

@section('title', 'Buat Jadwal WFH - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Buat Jadwal WFH
    </h1>

    <p class="text-secondary mb-0">
        Pilih tanggal Jumat dan tentukan personel yang mengikuti WFH.
    </p>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Jadwal belum dapat disimpan.</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    method="POST"
    action="{{ route('admin.wfh-schedules.store') }}"
>
    @csrf

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="mb-3">
                <label
                    for="wfh_date"
                    class="form-label fw-semibold"
                >
                    Tanggal WFH
                    <span class="text-danger">*</span>
                </label>

                <input
                    type="date"
                    id="wfh_date"
                    name="wfh_date"
                    value="{{ old('wfh_date', $suggestedDate) }}"
                    class="form-control
                        @error('wfh_date') is-invalid @enderror"
                    required
                >

                <div class="form-text">
                    Sistem hanya menerima tanggal yang jatuh pada hari Jumat.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Peserta WFH
                    <span class="text-danger">*</span>
                </label>

                <div class="form-check mb-2">
                    <input
                        type="radio"
                        id="selection_all"
                        name="selection_type"
                        value="all"
                        class="form-check-input"
                        @checked(
                            old('selection_type', 'all') === 'all'
                        )
                    >

                    <label
                        for="selection_all"
                        class="form-check-label"
                    >
                        Seluruh personel aktif
                    </label>
                </div>

                <div class="form-check">
                    <input
                        type="radio"
                        id="selection_selected"
                        name="selection_type"
                        value="selected"
                        class="form-check-input"
                        @checked(
                            old('selection_type') === 'selected'
                        )
                    >

                    <label
                        for="selection_selected"
                        class="form-check-label"
                    >
                        Pilih personel tertentu
                    </label>
                </div>

                <input
                    type="hidden"
                    id="is_all_personnel"
                    name="is_all_personnel"
                    value="{{
                        old('selection_type', 'all') === 'all'
                            ? '1'
                            : '0'
                    }}"
                >
            </div>

            <div class="mb-0">
                <label
                    for="notes"
                    class="form-label fw-semibold"
                >
                    Catatan
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    maxlength="1000"
                    class="form-control"
                    placeholder="Catatan tambahan, boleh dikosongkan"
                >{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <div
        id="personnelSelection"
        class="card border-0 shadow-sm mb-4"
    >
        <div class="card-header bg-white py-3">
            <div
                class="d-flex flex-column flex-md-row
                       justify-content-between gap-2"
            >
                <div>
                    <h2 class="h5 fw-bold mb-1">
                        Pilih Personel
                    </h2>

                    <small class="text-secondary">
                        Centang minimal satu personel.
                    </small>
                </div>

                <div class="d-flex gap-2">
                    <button
                        type="button"
                        id="selectAllPersonnel"
                        class="btn btn-sm btn-outline-success"
                    >
                        Pilih Semua
                    </button>

                    <button
                        type="button"
                        id="clearPersonnel"
                        class="btn btn-sm btn-outline-secondary"
                    >
                        Kosongkan
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            @forelse ($personnel as $person)
                <div class="form-check border-bottom py-3">
                    <input
                        type="checkbox"
                        id="person_{{ $person->id }}"
                        name="personnel_ids[]"
                        value="{{ $person->id }}"
                        class="form-check-input personnel-checkbox"
                        @checked(
                            in_array(
                                $person->id,
                                old('personnel_ids', [])
                            )
                        )
                    >

                    <label
                        for="person_{{ $person->id }}"
                        class="form-check-label w-100"
                    >
                        <span class="fw-semibold">
                            {{ $person->name }}
                        </span>

                        <br>

                        <small class="text-secondary">
                            {{ $person->login_id }} ·
                            {{ $person->rank }} ·
                            {{ $person->unit?->code ?? '-' }}
                        </small>
                    </label>
                </div>
            @empty
                <div class="text-center text-secondary py-4">
                    Belum ada akun Personel aktif.
                </div>
            @endforelse
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2">
        <button
            type="submit"
            class="btn btn-sikerja"
        >
            Simpan sebagai Draft
        </button>

        <a
            href="{{ route('admin.wfh-schedules.index') }}"
            class="btn btn-outline-secondary"
        >
            Batal
        </a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const allRadio = document.getElementById('selection_all');
    const selectedRadio = document.getElementById('selection_selected');
    const hiddenInput = document.getElementById('is_all_personnel');
    const selectionBox = document.getElementById('personnelSelection');
    const checkboxes = document.querySelectorAll('.personnel-checkbox');

    function updateSelectionMode() {
        const chooseAll = allRadio.checked;

        hiddenInput.value = chooseAll ? '1' : '0';
        selectionBox.style.display = chooseAll ? 'none' : 'block';

        checkboxes.forEach(function (checkbox) {
            checkbox.disabled = chooseAll;
        });
    }

    allRadio.addEventListener('change', updateSelectionMode);
    selectedRadio.addEventListener('change', updateSelectionMode);

    document
        .getElementById('selectAllPersonnel')
        .addEventListener('click', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = true;
            });
        });

    document
        .getElementById('clearPersonnel')
        .addEventListener('click', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = false;
            });
        });

    updateSelectionMode();
});
</script>
@endsection
