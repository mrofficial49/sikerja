@extends('layouts.app')

@section('title', 'Data Unit Kerja - SIKERJA')

@section('content')
<div
    class="d-flex flex-column flex-md-row
           justify-content-between align-items-md-center
           gap-3 mb-4"
>
    <div>
        <h1 class="h3 fw-bold mb-1">
            Data Unit Kerja
        </h1>

        <p class="text-secondary mb-0">
            Kelola unit atau bagian yang digunakan oleh personel.
        </p>
    </div>

    <a
        href="{{ route('admin.units.create') }}"
        class="btn btn-sikerja"
    >
        + Tambah Unit
    </a>
</div>

{{-- Form pencarian dan filter. --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form
            method="GET"
            action="{{ route('admin.units.index') }}"
            class="row g-3"
        >
            <div class="col-md-6">
                <label
                    for="search"
                    class="form-label fw-semibold"
                >
                    Pencarian
                </label>

                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="Cari kode atau nama unit"
                >
            </div>

            <div class="col-md-3">
                <label
                    for="status"
                    class="form-label fw-semibold"
                >
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                    class="form-select"
                >
                    <option value="">
                        Semua Status
                    </option>

                    <option
                        value="active"
                        @selected($status === 'active')
                    >
                        Aktif
                    </option>

                    <option
                        value="inactive"
                        @selected($status === 'inactive')
                    >
                        Tidak Aktif
                    </option>
                </select>
            </div>

            <div
                class="col-md-3 d-flex align-items-end gap-2"
            >
                <button
                    type="submit"
                    class="btn btn-sikerja flex-grow-1"
                >
                    Terapkan
                </button>

                <a
                    href="{{ route('admin.units.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table
                class="table table-hover align-middle mb-0"
            >
                <thead class="table-light">
                    <tr>
                        <th class="px-4">No.</th>
                        <th>Kode</th>
                        <th>Nama Unit</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th class="text-end px-4">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($units as $unit)
                        <tr>
                            <td class="px-4">
                                {{
                                    $units->firstItem()
                                    + $loop->index
                                }}
                            </td>

                            <td>
                                <span class="fw-semibold">
                                    {{ $unit->code }}
                                </span>
                            </td>

                            <td>
                                {{ $unit->name }}
                            </td>

                            <td class="text-secondary">
                                {{ $unit->description ?: '-' }}
                            </td>

                            <td>
                                @if ($unit->is_active)
                                    <span class="badge text-bg-success">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>

                            <td class="text-end px-4">
                                <div
                                    class="d-flex justify-content-end
                                           flex-wrap gap-2"
                                >
                                    <a
                                        href="{{
                                            route(
                                                'admin.units.edit',
                                                $unit
                                            )
                                        }}"
                                        class="btn btn-sm
                                               btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'admin.units.toggle-status',
                                                $unit
                                            )
                                        }}"
                                        onsubmit="
                                            return confirm(
                                                '{{ $unit->is_active
                                                    ? 'Nonaktifkan unit ini?'
                                                    : 'Aktifkan kembali unit ini?'
                                                }}'
                                            )
                                        "
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-sm
                                                {{
                                                    $unit->is_active
                                                    ? 'btn-outline-danger'
                                                    : 'btn-outline-success'
                                                }}"
                                        >
                                            {{
                                                $unit->is_active
                                                ? 'Nonaktifkan'
                                                : 'Aktifkan'
                                            }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="text-center py-5
                                       text-secondary"
                            >
                                Data unit tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($units->hasPages())
        <div class="card-footer bg-white">
            {{ $units->links() }}
        </div>
    @endif
</div>
@endsection
