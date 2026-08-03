<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WfhScheduleMember extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'schedule_id',
        'user_id',
        'member_status',
        'added_by',
        'is_schedule_change',
        'change_reason',
        'added_at',
        'checkin_deadline',
        'cancelled_at',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'is_schedule_change' => 'boolean',
            'added_at' => 'datetime',
            'checkin_deadline' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Jadwal WFH yang diikuti oleh personel.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(
            WfhSchedule::class,
            'schedule_id'
        );
    }

    /**
     * Personel yang mengikuti jadwal WFH.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /**
     * Admin yang memasukkan personel ke dalam jadwal.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'added_by'
        );
    }

    /**
     * Data presensi milik anggota jadwal.
     *
     * Satu anggota jadwal hanya memiliki satu presensi.
     */
    public function attendance(): HasOne
    {
        return $this->hasOne(
            Attendance::class,
            'schedule_member_id'
        );
    }

    /**
     * Laporan ketidakhadiran milik anggota jadwal.
     */
    public function absenceReport(): HasOne
    {
        return $this->hasOne(
            AbsenceReport::class,
            'schedule_member_id'
        );
    }

    /**
     * Laporan kerja milik anggota jadwal.
     */
    public function workReport(): HasOne
    {
        return $this->hasOne(
            WorkReport::class,
            'schedule_member_id'
        );
    }

    /**
     * Memeriksa apakah personel telah hadir.
     */
    public function isPresent(): bool
    {
        return $this->member_status === 'present';
    }

    /**
     * Memeriksa apakah personel tidak hadir.
     */
    public function isAbsent(): bool
    {
        return $this->member_status === 'absent';
    }
}
