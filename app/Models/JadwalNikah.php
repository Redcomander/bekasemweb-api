<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalNikah extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'penghulu_id',
        'pendaftaran_id',
        'lokasi',
        'catatan',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
    ];

    /**
     * Penghulu yang bertugas
     */
    public function penghulu(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penghulu_id');
    }

    /**
     * Pendaftaran nikah terkait
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranNikah::class, 'pendaftaran_id');
    }

    /**
     * Scope: available slots
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope: by date
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    /**
     * Scope: upcoming
     */
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai');
    }
}
