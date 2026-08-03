<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportNote extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() atau update().
     */
    protected $fillable = [
        'report_id',
        'leader_id',
        'note',
    ];

    /**
     * Laporan kerja yang diberi catatan.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(
            WorkReport::class,
            'report_id'
        );
    }

    /**
     * Pimpinan yang membuat catatan.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'leader_id'
        );
    }
}
