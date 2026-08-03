<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WfhSchedule extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'wfh_date',
        'status',
        'created_by',
        'is_all_personnel',
        'notes',
        'activated_at',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'wfh_date' => 'date',
            'is_all_personnel' => 'boolean',
            'activated_at' => 'datetime',
        ];
    }

    /**
     * Admin yang membuat jadwal WFH.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Daftar personel yang masuk dalam jadwal WFH.
     */
    public function members(): HasMany
    {
        return $this->hasMany(
            WfhScheduleMember::class,
            'schedule_id'
        );
    }

    /**
     * Membatasi query hanya untuk jadwal aktif.
     *
     * Contoh penggunaan:
     * WfhSchedule::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Memeriksa apakah jadwal sudah aktif.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Memeriksa apakah jadwal sudah selesai.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
