<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Antrian extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_antrian',
        'tanggal',
        'layanan',
        'nama_pengunjung',
        'no_hp',
        'keperluan',
        'nomor_urut',
        'status',
        'called_at',
        'completed_at',
        'served_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'called_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Boot untuk generate kode antrian
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($antrian) {
            if (empty($antrian->kode_antrian)) {
                $prefix = match($antrian->layanan) {
                    'pendaftaran_nikah' => 'A',
                    'konsultasi' => 'B',
                    'legalisir' => 'C',
                    'bimwin' => 'D',
                    default => 'E',
                };
                
                $lastNumber = static::whereDate('tanggal', $antrian->tanggal)
                    ->where('layanan', $antrian->layanan)
                    ->max('nomor_urut') ?? 0;
                
                $antrian->nomor_urut = $lastNumber + 1;
                $antrian->kode_antrian = sprintf('%s-%03d', $prefix, $antrian->nomor_urut);
            }
        });
    }

    /**
     * Petugas yang melayani
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    /**
     * Scope: hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', now()->toDateString());
    }

    /**
     * Scope: menunggu
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope: by layanan
     */
    public function scopeOfLayanan($query, string $layanan)
    {
        return $query->where('layanan', $layanan);
    }

    /**
     * Label layanan
     */
    public function getLayananLabelAttribute(): string
    {
        $labels = [
            'pendaftaran_nikah' => 'Pendaftaran Nikah',
            'konsultasi' => 'Konsultasi',
            'legalisir' => 'Legalisir Dokumen',
            'bimwin' => 'Bimbingan Perkawinan',
            'lainnya' => 'Layanan Lainnya',
        ];

        return $labels[$this->layanan] ?? $this->layanan;
    }
}
