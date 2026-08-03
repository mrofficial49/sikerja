{{-- Kode Unit --}}
<div class="mb-3">
    <label
        for="code"
        class="form-label fw-semibold"
    >
        Kode Unit
        <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        id="code"
        name="code"
        value="{{ old('code', $unit->code ?? '') }}"
        class="form-control
            @error('code') is-invalid @enderror"
        placeholder="Contoh: BINUM"
        maxlength="20"
        required
        autofocus
    >

    <div class="form-text">
        Gunakan huruf, angka, garis bawah, atau tanda hubung.
    </div>

    @error('code')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

{{-- Nama Unit --}}
<div class="mb-3">
    <label
        for="name"
        class="form-label fw-semibold"
    >
        Nama Unit
        <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        id="name"
        name="name"
        value="{{ old('name', $unit->name ?? '') }}"
        class="form-control
            @error('name') is-invalid @enderror"
        placeholder="Masukkan nama lengkap unit"
        maxlength="100"
        required
    >

    @error('name')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

{{-- Keterangan --}}
<div class="mb-4">
    <label
        for="description"
        class="form-label fw-semibold"
    >
        Keterangan
    </label>

    <textarea
        id="description"
        name="description"
        rows="4"
        maxlength="255"
        class="form-control
            @error('description') is-invalid @enderror"
        placeholder="Keterangan tambahan unit, boleh dikosongkan"
    >{{ old('description', $unit->description ?? '') }}</textarea>

    @error('description')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="d-flex flex-column flex-sm-row gap-2">
    <button
        type="submit"
        class="btn btn-sikerja"
    >
        {{ $submitLabel }}
    </button>

    <a
        href="{{ route('admin.units.index') }}"
        class="btn btn-outline-secondary"
    >
        Batal
    </a>
</div>
