<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkItem extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'report_id',
        'created_by',
        'source_type',
        'title',
        'description',
        'target_result',
        'status',
        'progress',
        'obstacle',
        'follow_up_plan',
        'continue_offline',
        'cancelled_by',
        'cancelled_at',
        'assigned_at',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'continue_offline' => 'boolean',
            'cancelled_at' => 'datetime',
            'assigned_at' => 'datetime',
        ];
    }

    /**
     * Laporan kerja yang memiliki kegiatan ini.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(
            WorkReport::class,
            'report_id'
        );
    }

    /**
     * Personel atau Pimpinan yang membuat kegiatan.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Pimpinan yang membatalkan tugas.
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
        );
    }

    /**
     * Daftar file PDF hasil pekerjaan.
     */
    public function files(): HasMany
    {
        return $this->hasMany(
            WorkItemFile::class,
            'item_id'
        );
    }

    /**
     * Memeriksa apakah kegiatan merupakan rencana pribadi.
     */
    public function isPersonalPlan(): bool
    {
        return $this->source_type === 'personal_plan';
    }

    /**
     * Memeriksa apakah kegiatan merupakan tugas Pimpinan.
     */
    public function isLeaderTask(): bool
    {
        return $this->source_type === 'leader_task';
    }

    /**
     * Memeriksa apakah pekerjaan sudah selesai.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Memeriksa apakah pekerjaan telah dibatalkan.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
