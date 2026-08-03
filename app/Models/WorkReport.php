<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkReport extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'schedule_member_id',
        'status',
        'submitted_at',
        'last_change_reason',
        'last_changed_at',
        'verified_by',
        'verified_at',
        'completed_offline_at',
        'locked_at',
        'is_locked',
    ];

    /**
     * Mengubah tanggal dan boolean menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'last_changed_at' => 'datetime',
            'verified_at' => 'datetime',
            'completed_offline_at' => 'datetime',
            'locked_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * Anggota jadwal yang memiliki laporan kerja.
     */
    public function scheduleMember(): BelongsTo
    {
        return $this->belongsTo(
            WfhScheduleMember::class,
            'schedule_member_id'
        );
    }

    /**
     * Pimpinan yang memverifikasi laporan.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    /**
     * Daftar pekerjaan di dalam laporan.
     */
    public function items(): HasMany
    {
        return $this->hasMany(
            WorkItem::class,
            'report_id'
        );
    }

    /**
     * Daftar catatan dari Pimpinan.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(
            ReportNote::class,
            'report_id'
        );
    }

    /**
     * Memeriksa apakah laporan masih berupa draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Memeriksa apakah laporan menunggu verifikasi.
     */
    public function isWaitingVerification(): bool
    {
        return $this->status === 'waiting_verification';
    }

    /**
     * Memeriksa apakah laporan sudah disetujui.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Memeriksa apakah laporan perlu direvisi.
     */
    public function needsRevision(): bool
    {
        return $this->status === 'needs_revision';
    }

    /**
     * Memeriksa apakah laporan sudah dikunci.
     */
    public function isLocked(): bool
    {
        return $this->is_locked;
    }
}
