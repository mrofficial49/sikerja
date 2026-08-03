<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'schedule_member_id',

        'checkin_at',
        'checkin_status',
        'checkin_reason',
        'checkin_latitude',
        'checkin_longitude',
        'checkin_photo_path',
        'checkin_photo_expires_at',
        'checkin_photo_deleted_at',

        'checkout_at',
        'checkout_status',
        'checkout_reason',
        'checkout_latitude',
        'checkout_longitude',
        'checkout_photo_path',
        'checkout_photo_expires_at',
        'checkout_photo_deleted_at',

        'attendance_status',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'checkin_at' => 'datetime',
            'checkin_latitude' => 'decimal:7',
            'checkin_longitude' => 'decimal:7',
            'checkin_photo_expires_at' => 'datetime',
            'checkin_photo_deleted_at' => 'datetime',

            'checkout_at' => 'datetime',
            'checkout_latitude' => 'decimal:7',
            'checkout_longitude' => 'decimal:7',
            'checkout_photo_expires_at' => 'datetime',
            'checkout_photo_deleted_at' => 'datetime',
        ];
    }

    /**
     * Anggota jadwal yang memiliki data presensi ini.
     */
    public function scheduleMember(): BelongsTo
    {
        return $this->belongsTo(
            WfhScheduleMember::class,
            'schedule_member_id'
        );
    }

    /**
     * Memeriksa apakah personel sudah melakukan check-in.
     */
    public function hasCheckedIn(): bool
    {
        return $this->checkin_at !== null;
    }

    /**
     * Memeriksa apakah personel sudah melakukan check-out.
     */
    public function hasCheckedOut(): bool
    {
        return $this->checkout_at !== null;
    }

    /**
     * Memeriksa apakah presensi sudah lengkap.
     */
    public function isComplete(): bool
    {
        return $this->hasCheckedIn()
            && $this->hasCheckedOut()
            && $this->attendance_status === 'present';
    }
}
