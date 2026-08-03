<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceReport extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'schedule_member_id',
        'absence_type',
        'reason',
        'photo_path',
        'photo_expires_at',
        'photo_deleted_at',
        'submitted_at',
        'is_locked',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'photo_expires_at' => 'datetime',
            'photo_deleted_at' => 'datetime',
            'submitted_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * Anggota jadwal yang mengirim laporan ketidakhadiran.
     */
    public function scheduleMember(): BelongsTo
    {
        return $this->belongsTo(
            WfhScheduleMember::class,
            'schedule_member_id'
        );
    }

    /**
     * Memeriksa apakah laporan sudah dikunci.
     */
    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    /**
     * Memeriksa apakah bukti foto masih tersedia.
     */
    public function hasAvailablePhoto(): bool
    {
        return $this->photo_path !== null
            && $this->photo_deleted_at === null;
    }
}
