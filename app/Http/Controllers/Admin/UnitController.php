<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * Menampilkan daftar unit kerja.
     */
    public function index(Request $request): View
    {
        /*
         * Mengambil parameter pencarian dan filter
         * dari URL.
         */
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $units = Unit::query()
            /*
             * Cari berdasarkan kode atau nama unit.
             */
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })

            /*
             * Filter unit aktif atau tidak aktif.
             */
            ->when($status === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($status === 'inactive', function ($query) {
                $query->where('is_active', false);
            })

            // Unit aktif ditampilkan lebih dahulu.
            ->orderByDesc('is_active')
            ->orderBy('name')

            // Tampilkan maksimal 20 data setiap halaman.
            ->paginate(20)
            ->withQueryString();

        return view('admin.units.index', compact(
            'units',
            'search',
            'status'
        ));
    }

    /**
     * Menampilkan formulir tambah unit.
     */
    public function create(): View
    {
        return view('admin.units.create');
    }

    /**
     * Menyimpan unit baru.
     */
    public function store(Request $request): RedirectResponse
    {
        /*
         * Kode unit diseragamkan menjadi huruf kapital.
         */
        $request->merge([
            'code' => strtoupper(
                trim((string) $request->input('code'))
            ),
        ]);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('units', 'code'),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
        ], [
            'code.required' => 'Kode unit wajib diisi.',
            'code.max' => 'Kode unit maksimal 20 karakter.',
            'code.regex' =>
                'Kode unit hanya boleh berisi huruf, angka, garis bawah, atau tanda hubung.',
            'code.unique' => 'Kode unit sudah digunakan.',
            'name.required' => 'Nama unit wajib diisi.',
            'name.max' => 'Nama unit maksimal 100 karakter.',
            'description.max' =>
                'Keterangan maksimal 255 karakter.',
        ]);

        /*
         * Unit baru langsung dibuat aktif.
         */
        $validated['is_active'] = true;

        $unit = Unit::create($validated);

        $this->writeLog(
            $request,
            'unit_created',
            "Admin menambahkan unit {$unit->code} - {$unit->name}.",
            $unit
        );

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir edit unit.
     *
     * Laravel otomatis mencari Unit berdasarkan ID
     * dari parameter URL {unit}.
     */
    public function edit(Unit $unit): View
    {
        return view('admin.units.edit', compact('unit'));
    }

    /**
     * Memperbarui data unit.
     */
    public function update(
        Request $request,
        Unit $unit
    ): RedirectResponse {
        $request->merge([
            'code' => strtoupper(
                trim((string) $request->input('code'))
            ),
        ]);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',

                /*
                 * Kode harus unik, tetapi kode milik unit
                 * yang sedang diedit boleh tetap digunakan.
                 */
                Rule::unique('units', 'code')
                    ->ignore($unit->id),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
        ], [
            'code.required' => 'Kode unit wajib diisi.',
            'code.max' => 'Kode unit maksimal 20 karakter.',
            'code.regex' =>
                'Kode unit hanya boleh berisi huruf, angka, garis bawah, atau tanda hubung.',
            'code.unique' => 'Kode unit sudah digunakan.',
            'name.required' => 'Nama unit wajib diisi.',
            'name.max' => 'Nama unit maksimal 100 karakter.',
            'description.max' =>
                'Keterangan maksimal 255 karakter.',
        ]);

        $unit->update($validated);

        $this->writeLog(
            $request,
            'unit_updated',
            "Admin memperbarui unit {$unit->code} - {$unit->name}.",
            $unit
        );

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Data unit berhasil diperbarui.');
    }

    /**
     * Mengaktifkan atau menonaktifkan unit.
     *
     * Unit tidak dihapus permanen agar riwayat
     * personel dan laporan tetap terhubung.
     */
    public function toggleStatus(
        Request $request,
        Unit $unit
    ): RedirectResponse {
        $newStatus = ! $unit->is_active;

        $unit->update([
            'is_active' => $newStatus,
        ]);

        $action = $newStatus
            ? 'unit_activated'
            : 'unit_deactivated';

        $statusText = $newStatus
            ? 'mengaktifkan'
            : 'menonaktifkan';

        $this->writeLog(
            $request,
            $action,
            "Admin {$statusText} unit {$unit->code} - {$unit->name}.",
            $unit
        );

        return back()->with(
            'success',
            $newStatus
                ? 'Unit berhasil diaktifkan kembali.'
                : 'Unit berhasil dinonaktifkan.'
        );
    }

    /**
     * Menulis aktivitas Admin ke activity_logs.
     */
    private function writeLog(
        Request $request,
        string $action,
        string $description,
        Unit $unit
    ): void {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => Unit::class,
            'subject_id' => $unit->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
