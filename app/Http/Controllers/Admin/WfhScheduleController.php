<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\User;
use App\Models\WfhSchedule;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WfhScheduleController extends Controller
{
    /**
     * Menampilkan daftar jadwal WFH.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $month = $request->input('month');
        $year = $request->input('year');

        $schedules = WfhSchedule::query()
            ->with('creator')
            ->withCount('members')

            // Filter berdasarkan status jadwal.
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })

            // Filter berdasarkan bulan.
            ->when($month, function ($query) use ($month) {
                $query->whereMonth('wfh_date', $month);
            })

            // Filter berdasarkan tahun.
            ->when($year, function ($query) use ($year) {
                $query->whereYear('wfh_date', $year);
            })

            ->orderByDesc('wfh_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.wfh-schedules.index', compact(
            'schedules',
            'status',
            'month',
            'year'
        ));
    }

    /**
     * Menampilkan formulir pembuatan jadwal.
     */
    public function create(): View
    {
        /*
         * Hanya akun aktif dengan role Personel
         * yang dapat dimasukkan ke jadwal WFH.
         */
        $personnel = User::query()
            ->with('unit')
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('name', 'Personel');
            })
            ->orderBy('name')
            ->get();

        /*
         * Menghitung Jumat berikutnya untuk membantu Admin.
         */
        $suggestedDate = now('Asia/Jakarta')
            ->next(Carbon::FRIDAY)
            ->format('Y-m-d');

        return view('admin.wfh-schedules.create', compact(
            'personnel',
            'suggestedDate'
        ));
    }

    /**
     * Menyimpan jadwal baru beserta anggota jadwal.
     */
    public function store(Request $request): RedirectResponse
    {
        /*
         * Checkbox HTML tidak mengirim nilai ketika tidak dicentang.
         * boolean() mengubah nilainya menjadi true atau false.
         */
        $request->merge([
            'is_all_personnel' => $request->boolean(
                'is_all_personnel'
            ),
        ]);

        $validated = $request->validate([
            'wfh_date' => [
                'required',
                'date',
                Rule::unique('wfh_schedules', 'wfh_date'),
            ],

            'is_all_personnel' => [
                'required',
                'boolean',
            ],

            'personnel_ids' => [
                'nullable',
                'array',
            ],

            'personnel_ids.*' => [
                'integer',
                'distinct',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'wfh_date.required' => 'Tanggal WFH wajib dipilih.',
            'wfh_date.date' => 'Format tanggal WFH tidak valid.',
            'wfh_date.unique' =>
                'Jadwal WFH pada tanggal tersebut sudah tersedia.',

            'personnel_ids.array' =>
                'Daftar personel yang dipilih tidak valid.',

            'personnel_ids.*.integer' =>
                'Data personel yang dipilih tidak valid.',

            'personnel_ids.*.distinct' =>
                'Terdapat personel yang dipilih lebih dari satu kali.',

            'notes.max' => 'Catatan maksimal 1.000 karakter.',
        ]);

        $wfhDate = Carbon::parse(
            $validated['wfh_date'],
            'Asia/Jakarta'
        )->startOfDay();

       /*
 * Dalam penggunaan normal, jadwal WFH hanya boleh
 * dibuat untuk hari Jumat.
 *
 * Saat PRESENTATION_MODE aktif, pengecekan hari Jumat
 * dilewati agar aplikasi dapat didemokan pada hari lain.
 */
if (
    ! config('app.presentation_mode')
    && ! $wfhDate->isFriday()
) {
    throw ValidationException::withMessages([
        'wfh_date' =>
            'Jadwal WFH hanya dapat dibuat pada hari Jumat.',
    ]); }

        /*
         * Mencegah Admin membuat jadwal untuk tanggal lampau.
         */
        if ($wfhDate->isBefore(
            now('Asia/Jakarta')->startOfDay()
        )) {
            throw ValidationException::withMessages([
                'wfh_date' =>
                    'Jadwal WFH tidak dapat dibuat untuk tanggal lampau.',
            ]);
        }

        $isAllPersonnel = (bool) $validated['is_all_personnel'];

        $personnelQuery = User::query()
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('name', 'Personel');
            });

        /*
         * Jika bukan seluruh personel, hanya ambil ID
         * yang dipilih pada formulir.
         */
        if (! $isAllPersonnel) {
            $selectedIds = array_map(
                'intval',
                $validated['personnel_ids'] ?? []
            );

            if (count($selectedIds) === 0) {
                throw ValidationException::withMessages([
                    'personnel_ids' =>
                        'Pilih minimal satu personel.',
                ]);
            }

            $personnelQuery->whereIn('id', $selectedIds);
        }

        $selectedPersonnel = $personnelQuery->get();

        if ($selectedPersonnel->isEmpty()) {
            throw ValidationException::withMessages([
                'personnel_ids' =>
                    'Tidak ada personel aktif yang dapat dijadwalkan.',
            ]);
        }

        /*
         * Jika memilih personel tertentu, pastikan seluruh ID
         * yang dipilih benar-benar merupakan Personel aktif.
         */
        if (! $isAllPersonnel) {
            $requestedCount = count(array_unique(
                $validated['personnel_ids'] ?? []
            ));

            if ($selectedPersonnel->count() !== $requestedCount) {
                throw ValidationException::withMessages([
                    'personnel_ids' =>
                        'Terdapat akun tidak aktif atau bukan Personel.',
                ]);
            }
        }

        $schedule = DB::transaction(function () use (
            $request,
            $validated,
            $wfhDate,
            $isAllPersonnel,
            $selectedPersonnel
        ) {
            /*
             * Jadwal disimpan sebagai draft agar Admin
             * dapat memeriksanya sebelum diaktifkan.
             */
            $schedule = WfhSchedule::create([
                'wfh_date' => $wfhDate->toDateString(),
                'status' => 'draft',
                'created_by' => $request->user()->id,
                'is_all_personnel' => $isAllPersonnel,
                'notes' => $validated['notes'] ?? null,
                'activated_at' => null,
            ]);

            /*
             * Batas normal check-in adalah pukul 08.00
             * pada tanggal pelaksanaan WFH.
             */
            $checkinDeadline = $wfhDate
                ->copy()
                ->setTime(8, 0, 0);

            foreach ($selectedPersonnel as $person) {
                $schedule->members()->create([
                    'user_id' => $person->id,
                    'member_status' => 'scheduled',
                    'added_by' => $request->user()->id,
                    'is_schedule_change' => false,
                    'change_reason' => null,
                    'added_at' => now('Asia/Jakarta'),
                    'checkin_deadline' => $checkinDeadline,
                    'cancelled_at' => null,
                ]);
            }

            return $schedule;
        });

        $this->writeLog(
            $request,
            'wfh_schedule_created',
            'Admin membuat jadwal WFH tanggal '
                . $wfhDate->format('d-m-Y')
                . ' dengan '
                . $selectedPersonnel->count()
                . ' personel.',
            $schedule
        );

        return redirect()
            ->route('admin.wfh-schedules.show', $schedule)
            ->with(
                'success',
                'Jadwal WFH berhasil dibuat sebagai draft.'
            );
    }

    /**
     * Menampilkan detail jadwal dan daftar anggota.
     */
    public function show(
        Request $request,
        WfhSchedule $wfhSchedule
    ): View {
        $wfhSchedule->load('creator');

        $members = $wfhSchedule->members()
            ->with(['user.unit', 'attendance'])
            ->orderBy('member_status')
            ->orderBy(
                User::select('name')
                    ->whereColumn(
                        'users.id',
                        'wfh_schedule_members.user_id'
                    )
            )
            ->paginate(20)
            ->withQueryString();

        /*
         * Mengambil ID personel yang masih aktif
         * sebagai anggota jadwal.
         */
        $registeredUserIds = $wfhSchedule->members()
            ->where('member_status', '!=', 'cancelled')
            ->pluck('user_id');

        /*
         * Personel yang belum terdaftar atau sebelumnya
         * dibatalkan dapat dipilih kembali oleh Admin.
         */
        $availablePersonnel = User::query()
            ->with('unit')
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('name', 'Personel');
            })
            ->whereNotIn('id', $registeredUserIds)
            ->orderBy('name')
            ->get();

        return view('admin.wfh-schedules.show', compact(
            'wfhSchedule',
            'members',
            'availablePersonnel'
        ));
    }

    /**
     * Mengaktifkan jadwal WFH.
     */
    public function activate(
        Request $request,
        WfhSchedule $wfhSchedule
    ): RedirectResponse {
        if ($wfhSchedule->status !== 'draft') {
            return back()->with(
                'error',
                'Hanya jadwal berstatus draft yang dapat diaktifkan.'
            );
        }

        if (! $wfhSchedule->members()->exists()) {
            return back()->with(
                'error',
                'Jadwal tidak memiliki anggota.'
            );
        }

        if (
            $wfhSchedule->wfh_date->isBefore(
                now('Asia/Jakarta')->startOfDay()
            )
        ) {
            return back()->with(
                'error',
                'Jadwal lampau tidak dapat diaktifkan.'
            );
        }

        $wfhSchedule->update([
            'status' => 'active',
            'activated_at' => now('Asia/Jakarta'),
        ]);
        /*
 * Setelah jadwal resmi diaktifkan, kirim notifikasi
 * kepada seluruh Personel yang menjadi anggota jadwal.
 *
 * Notifikasi tidak dikirim saat status masih draft,
 * karena Admin masih dapat memeriksa atau mengubah jadwal.
 */
$wfhSchedule->members()
    ->where('member_status', '!=', 'cancelled')
    ->with('user')
    ->get()
    ->each(function ($member) use ($wfhSchedule) {

        /*
         * Pastikan anggota masih memiliki akun user.
         */
        if (! $member->user) {
            return;
        }

        AppNotification::create([
            'user_id' => $member->user_id,

            'type' => 'wfh_schedule',

            'title' => 'Jadwal WFH Aktif',

            'message' =>
                'Anda dijadwalkan mengikuti WFH pada '
                . $wfhSchedule->wfh_date
                    ->translatedFormat('d F Y')
                . '. Jadwal telah diaktifkan oleh Admin.',

            'related_type' => WfhSchedule::class,

            'related_id' => $wfhSchedule->id,

            'is_read' => false,

            'read_at' => null,
        ]);
    });

        $this->writeLog(
            $request,
            'wfh_schedule_activated',
            'Admin mengaktifkan jadwal WFH tanggal '
                . $wfhSchedule->wfh_date->format('d-m-Y')
                . '.',
            $wfhSchedule
        );

        return back()->with(
            'success',
            'Jadwal WFH berhasil diaktifkan.'
        );
    }

    /**
     * Membatalkan jadwal WFH.
     */
    public function cancel(
        Request $request,
        WfhSchedule $wfhSchedule
    ): RedirectResponse {
        if (
            in_array(
                $wfhSchedule->status,
                ['completed', 'cancelled'],
                true
            )
        ) {
            return back()->with(
                'error',
                'Jadwal tersebut tidak dapat dibatalkan.'
            );
        }

        /*
         * Jadwal tidak dapat dibatalkan apabila sudah ada
         * anggota yang melakukan check-in.
         */
        $hasCheckedInMember = $wfhSchedule->members()
            ->whereHas('attendance', function ($query) {
                $query->whereNotNull('checkin_at');
            })
            ->exists();

        if ($hasCheckedInMember) {
            return back()->with(
                'error',
                'Jadwal tidak dapat dibatalkan karena sudah ada personel yang check-in.'
            );
        }

        DB::transaction(function () use ($wfhSchedule) {
            $wfhSchedule->update([
                'status' => 'cancelled',
            ]);

            /*
             * Batalkan seluruh anggota yang masih aktif,
             * termasuk anggota perubahan jadwal.
             */
            $wfhSchedule->members()
                ->whereIn('member_status', [
                    'scheduled',
                    'schedule_change',
                ])
                ->update([
                    'member_status' => 'cancelled',
                    'cancelled_at' => now('Asia/Jakarta'),
                    'updated_at' => now('Asia/Jakarta'),
                ]);
        });

        $this->writeLog(
            $request,
            'wfh_schedule_cancelled',
            'Admin membatalkan jadwal WFH tanggal '
                . $wfhSchedule->wfh_date->format('d-m-Y')
                . '.',
            $wfhSchedule
        );

        return back()->with(
            'success',
            'Jadwal WFH berhasil dibatalkan.'
        );
    }

    /**
     * Menggunakan kembali jadwal yang telah dibatalkan.
     *
     * Jadwal dikembalikan menjadi draft agar Admin
     * dapat memeriksa anggota sebelum mengaktifkannya.
     */
    public function restore(
        Request $request,
        WfhSchedule $wfhSchedule
    ): RedirectResponse {
        /*
         * Hanya jadwal berstatus cancelled yang
         * dapat digunakan kembali.
         */
        if ($wfhSchedule->status !== 'cancelled') {
            return back()->with(
                'error',
                'Hanya jadwal yang dibatalkan yang dapat digunakan kembali.'
            );
        }

        $now = now('Asia/Jakarta');

        $scheduleDate = Carbon::parse(
            $wfhSchedule->wfh_date->format('Y-m-d'),
            'Asia/Jakarta'
        )->startOfDay();

        /*
         * Jadwal lampau tidak boleh digunakan kembali.
         */
        if (
            $scheduleDate->isBefore(
                $now->copy()->startOfDay()
            )
        ) {
            return back()->with(
                'error',
                'Jadwal lampau tidak dapat digunakan kembali.'
            );
        }

        $normalDeadline = $scheduleDate
            ->copy()
            ->setTime(8, 0, 0);

        /*
         * Jika dipulihkan pada tanggal pelaksanaan
         * setelah pukul 08.00, personel mendapatkan
         * batas check-in baru selama 30 menit.
         */
        $isLateRestore = $scheduleDate->isSameDay($now)
            && $now->greaterThan($normalDeadline);

        $newDeadline = $isLateRestore
            ? $now->copy()->addMinutes(30)
            : $normalDeadline;

        DB::transaction(function () use (
            $request,
            $wfhSchedule,
            $now,
            $newDeadline,
            $isLateRestore
        ) {
            /*
             * Kembalikan jadwal menjadi draft.
             */
            $wfhSchedule->update([
                'status' => 'draft',
                'activated_at' => null,
            ]);

            /*
             * Pulihkan seluruh anggota yang sebelumnya
             * dibatalkan bersama jadwal.
             */
            $cancelledMembers = $wfhSchedule->members()
                ->with('user')
                ->where('member_status', 'cancelled')
                ->get();

            foreach ($cancelledMembers as $member) {
                /*
                 * Jika jadwal dipulihkan setelah pukul 08.00,
                 * status dianggap sebagai perubahan jadwal.
                 *
                 * Jika belum pukul 08.00, status lama ditentukan
                 * dari nilai is_schedule_change.
                 */
                $memberStatus = $isLateRestore
                    ? 'schedule_change'
                    : (
                        $member->is_schedule_change
                            ? 'schedule_change'
                            : 'scheduled'
                    );

                $changeReason = $isLateRestore
                    ? (
                        $member->change_reason
                        ?: 'Jadwal digunakan kembali setelah sebelumnya dibatalkan.'
                    )
                    : $member->change_reason;

                $member->update([
                    'member_status' => $memberStatus,
                    'added_by' => $request->user()->id,
                    'is_schedule_change' => $isLateRestore
                        ? true
                        : $member->is_schedule_change,
                    'change_reason' => $changeReason,
                    'added_at' => $now,
                    'checkin_deadline' => $newDeadline,
                    'cancelled_at' => null,
                ]);

                /*
                 * Beri notifikasi kepada personel bahwa
                 * jadwal digunakan kembali.
                 */
                if ($member->user) {
                    AppNotification::create([
                        'user_id' => $member->user_id,
                        'type' => 'wfh_schedule_restored',
                        'title' => 'Jadwal WFH Digunakan Kembali',
                        'message' =>
                            'Jadwal WFH tanggal '
                            . $wfhSchedule->wfh_date
                                ->translatedFormat('d F Y')
                            . ' digunakan kembali oleh Admin.'
                            . (
                                $isLateRestore
                                    ? ' Batas check-in baru pukul '
                                        . $newDeadline->format('H:i')
                                        . ' WIB.'
                                    : ''
                            ),
                        'related_type' => WfhSchedule::class,
                        'related_id' => $wfhSchedule->id,
                        'is_read' => false,
                        'read_at' => null,
                    ]);
                }
            }
        });

        $this->writeLog(
            $request,
            'wfh_schedule_restored',
            'Admin menggunakan kembali jadwal WFH tanggal '
                . $wfhSchedule->wfh_date->format('d-m-Y')
                . '.',
            $wfhSchedule
        );

        return redirect()
            ->route(
                'admin.wfh-schedules.show',
                $wfhSchedule
            )
            ->with(
                'success',
                'Jadwal berhasil digunakan kembali dan berstatus draft.'
            );
    }

    /**
     * Menulis aktivitas Admin ke activity_logs.
     */
    private function writeLog(
        Request $request,
        string $action,
        string $description,
        WfhSchedule $schedule
    ): void {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => WfhSchedule::class,
            'subject_id' => $schedule->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
