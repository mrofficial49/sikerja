<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     *
     * login_id digunakan sebagai:
     * - NRP/NIP untuk Personel.
     * - ID khusus untuk Admin dan Pimpinan.
     */
    protected $fillable = [
        'role_id',
        'unit_id',
        'login_id',
        'name',
        'rank',
        'position',
        'password',
        'must_change_password',
        'is_active',
        'last_login_at',
    ];

    /**
     * Kolom yang tidak boleh ditampilkan ketika data User
     * diubah menjadi array atau JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mengubah nilai database menjadi tipe data PHP.
     *
     * Cast "hashed" membuat password otomatis di-hash
     * ketika disimpan melalui model User.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Setiap pengguna memiliki satu role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Setiap pengguna dapat memiliki satu unit.
     *
     * Untuk akun Admin dan Pimpinan, unit_id boleh kosong.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Jadwal WFH yang dibuat oleh pengguna.
     *
     * Relasi ini biasanya digunakan oleh akun Admin.
     */
    public function createdWfhSchedules(): HasMany
    {
        return $this->hasMany(
            WfhSchedule::class,
            'created_by'
        );
    }

    /**
     * Daftar jadwal WFH yang diikuti pengguna.
     */
    public function wfhScheduleMemberships(): HasMany
    {
        return $this->hasMany(
            WfhScheduleMember::class,
            'user_id'
        );
    }

    /**
     * Daftar anggota jadwal yang ditambahkan pengguna.
     *
     * Relasi ini biasanya digunakan oleh Admin.
     */
    public function addedWfhScheduleMembers(): HasMany
    {
        return $this->hasMany(
            WfhScheduleMember::class,
            'added_by'
        );
    }


    /**
     * Laporan kerja yang diverifikasi oleh pengguna.
     *
     * Relasi ini digunakan oleh akun Pimpinan.
     */
    public function verifiedWorkReports(): HasMany
    {
        return $this->hasMany(
            WorkReport::class,
            'verified_by'
        );
    }

    /**
     * Daftar rencana kerja atau tugas yang dibuat pengguna.
     */
    public function createdWorkItems(): HasMany
    {
        return $this->hasMany(
            WorkItem::class,
            'created_by'
        );
    }

    /**
     * Daftar tugas yang dibatalkan oleh Pimpinan.
     */
    public function cancelledWorkItems(): HasMany
    {
        return $this->hasMany(
            WorkItem::class,
            'cancelled_by'
        );
    }

    /**
     * Daftar file hasil pekerjaan yang diunggah pengguna.
     */
    public function uploadedWorkItemFiles(): HasMany
    {
        return $this->hasMany(
            WorkItemFile::class,
            'uploaded_by'
        );
    }

    /**
     * Daftar catatan laporan yang dibuat Pimpinan.
     */
    public function reportNotes(): HasMany
    {
        return $this->hasMany(
            ReportNote::class,
            'leader_id'
        );
    }


    /**
     * Notifikasi dalam aplikasi milik pengguna.
     *
     * Nama appNotifications digunakan agar tidak berbenturan
     * dengan fitur Notification bawaan Laravel.
     */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(
            AppNotification::class,
            'user_id'
        );
    }

    /**
     * Pengumuman yang dibuat oleh pengguna.
     *
     * Relasi ini biasanya digunakan oleh akun Admin.
     */
    public function createdAnnouncements(): HasMany
    {
        return $this->hasMany(
            Announcement::class,
            'created_by'
        );
    }

    /**
     * Riwayat aktivitas milik pengguna.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(
            ActivityLog::class,
            'user_id'
        );
    }


    /**
     * Memeriksa apakah pengguna adalah Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === 'Admin';
    }

    /**
     * Memeriksa apakah pengguna adalah Pimpinan.
     */
    public function isLeader(): bool
    {
        return $this->role?->name === 'Pimpinan';
    }

    /**
     * Memeriksa apakah pengguna adalah Personel.
     */
    public function isPersonnel(): bool
    {
        return $this->role?->name === 'Personel';
    }
}
