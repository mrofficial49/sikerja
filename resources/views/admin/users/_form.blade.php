@if ($isEdit)
    <div class="mb-3">
        <label class="form-label fw-semibold">
            ID Login
        </label>

        <input
            type="text"
            value="{{ $user->login_id }}"
            class="form-control"
            disabled
        >

        <div class="form-text">
            ID Login bersifat permanen dan tidak dapat diubah.
        </div>
    </div>
@else
    <div class="mb-3">
        <label
            for="login_id"
            class="form-label fw-semibold"
        >
            ID Login
            <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            id="login_id"
            name="login_id"
            value="{{ old('login_id') }}"
            class="form-control
                @error('login_id') is-invalid @enderror"
            placeholder="NRP, NIP, atau ID khusus"
            maxlength="50"
            required
            autofocus
        >

        @error('login_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label
                for="role_id"
                class="form-label fw-semibold"
            >
                Role
                <span class="text-danger">*</span>
            </label>

            <select
                id="role_id"
                name="role_id"
                class="form-select
                    @error('role_id') is-invalid @enderror"
                required
            >
                <option value="">Pilih Role</option>

                @foreach ($roles as $role)
                    <option
                        value="{{ $role->id }}"
                        @selected(
                            old('role_id', $user->role_id ?? '')
                            == $role->id
                        )
                    >
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

            @error('role_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label
                for="unit_id"
                class="form-label fw-semibold"
            >
                Unit Kerja
            </label>

            <select
                id="unit_id"
                name="unit_id"
                class="form-select
                    @error('unit_id') is-invalid @enderror"
            >
                <option value="">Tidak terikat unit</option>

                @foreach ($units as $unit)
                    <option
                        value="{{ $unit->id }}"
                        @selected(
                            old('unit_id', $user->unit_id ?? '')
                            == $unit->id
                        )
                    >
                        {{ $unit->code }} - {{ $unit->name }}
                    </option>
                @endforeach
            </select>

            <div class="form-text">
                Unit wajib dipilih untuk akun Personel.
            </div>

            @error('unit_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label
        for="name"
        class="form-label fw-semibold"
    >
        Nama Lengkap
        <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        id="name"
        name="name"
        value="{{ old('name', $user->name ?? '') }}"
        class="form-control
            @error('name') is-invalid @enderror"
        maxlength="150"
        required
    >

    @error('name')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label
                for="rank"
                class="form-label fw-semibold"
            >
                Pangkat/Golongan
                <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                id="rank"
                name="rank"
                value="{{ old('rank', $user->rank ?? '') }}"
                class="form-control
                    @error('rank') is-invalid @enderror"
                maxlength="100"
                required
            >

            @error('rank')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-4">
            <label
                for="position"
                class="form-label fw-semibold"
            >
                Jabatan
                <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                id="position"
                name="position"
                value="{{ old('position', $user->position ?? '') }}"
                class="form-control
                    @error('position') is-invalid @enderror"
                maxlength="150"
                required
            >

            @error('position')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>

@if (! $isEdit)
    <div class="alert alert-info">
        Password sementara akan dibuat otomatis setelah akun disimpan.
        Pengguna wajib menggantinya saat pertama kali login.
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
        href="{{ route('admin.users.index') }}"
        class="btn btn-outline-secondary"
    >
        Batal
    </a>
</div>
