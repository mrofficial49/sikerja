<div class="mb-3">
    <label
        for="title"
        class="form-label fw-semibold"
    >
        Judul Rencana Kerja
        <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        id="title"
        name="title"
        value="{{ old('title', $workItem->title ?? '') }}"
        class="form-control
            @error('title') is-invalid @enderror"
        maxlength="200"
        placeholder="Contoh: Menyusun laporan kegiatan mingguan"
        required
        autofocus
    >

    @error('title')
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
        Uraian Pekerjaan
        <span class="text-danger">*</span>
    </label>

    <textarea
        id="description"
        name="description"
        rows="5"
        maxlength="5000"
        class="form-control
            @error('description') is-invalid @enderror"
        placeholder="Jelaskan pekerjaan yang akan dilaksanakan"
        required
    >{{ old('description', $workItem->description ?? '') }}</textarea>

    @error('description')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="mb-3">
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
        maxlength="5000"
        class="form-control
            @error('target_result') is-invalid @enderror"
        placeholder="Jelaskan hasil yang diharapkan"
        required
    >{{ old('target_result', $workItem->target_result ?? '') }}</textarea>

    @error('target_result')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

@if ($requiresChangeReason)
    <div class="mb-4">
        <label
            for="change_reason"
            class="form-label fw-semibold"
        >
            Alasan Perubahan
            <span class="text-danger">*</span>
        </label>

        <textarea
            id="change_reason"
            name="change_reason"
            rows="3"
            maxlength="1000"
            class="form-control
                @error('change_reason') is-invalid @enderror"
            placeholder="Jelaskan alasan perubahan laporan"
            required
        >{{ old('change_reason') }}</textarea>

        <div class="form-text text-danger">
            Laporan sudah pernah dikirim. Setelah diubah,
            status laporan kembali menjadi draft.
        </div>

        @error('change_reason')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
@endif

<div class="d-flex flex-column flex-sm-row gap-2">
    <button
        type="submit"
        class="btn btn-sikerja"
    >
        {{ $submitLabel }}
    </button>

    <a
        href="{{ route('personnel.work-items.index') }}"
        class="btn btn-outline-secondary"
    >
        Batal
    </a>
</div>
