<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PendaftaranNikah extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_pendaftaran',
        // Data Pria
        'nama_pria',
        'nik_pria',
        'tempat_lahir_pria',
        'tanggal_lahir_pria',
        'alamat_pria',
        'no_hp_pria',
        'pekerjaan_pria',
        'status_pria',
        // Data Wanita
        'nama_wanita',
        'nik_wanita',
        'tempat_lahir_wanita',
        'tanggal_lahir_wanita',
        'alamat_wanita',
        'no_hp_wanita',
        'pekerjaan_wanita',
        'status_wanita',
        // Data Wali
        'nama_wali',
        'hubungan_wali',
        'no_hp_wali',
        // Rencana Nikah
        'tanggal_nikah',
        'jam_nikah',
        'lokasi_nikah',
        'alamat_nikah',
        'mahar',
        'mahar_keterangan',
        // Status
        'status',
        'catatan_admin',
        'penghulu_id',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal_lahir_pria' => 'date',
        'tanggal_lahir_wanita' => 'date',
        'tanggal_nikah' => 'date',
        'jam_nikah' => 'datetime:H:i',
        'mahar' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    /**
     * Boot untuk generate kode pendaftaran
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pendaftaran) {
            if (empty($pendaftaran->kode_pendaftaran)) {
                $year = now()->format('Y');
                $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
                $pendaftaran->kode_pendaftaran = sprintf('PN-%s-%04d', $year, $lastId + 1);
            }
        });
    }

    /**
     * Dokumen pendaftaran
     */
    public function dokumens(): HasMany
    {
        return $this->hasMany(DokumenNikah::class, 'pendaftaran_id');
    }

    /**
     * Jadwal nikah
     */
    public function jadwal(): HasOne
    {
        return $this->hasOne(JadwalNikah::class, 'pendaftaran_id');
    }

    /**
     * Penghulu yang ditugaskan
     */
    public function penghulu(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penghulu_id');
    }

    /**
     * Admin yang memverifikasi
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope: berdasarkan status
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: pending verifikasi
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['diajukan', 'verifikasi']);
    }
}
