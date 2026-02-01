<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenNikah extends Model
{
    use HasFactory;

    protected $fillable = [
        'pendaftaran_id',
        'jenis',
        'file_path',
        'original_name',
        'status',
        'catatan',
    ];

    /**
     * Pendaftaran nikah
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranNikah::class, 'pendaftaran_id');
    }

    /**
     * Scope: berdasarkan status
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Label jenis dokumen
     */
    public function getJenisLabelAttribute(): string
    {
        $labels = [
            'ktp_pria' => 'KTP Calon Suami',
            'ktp_wanita' => 'KTP Calon Istri',
            'kk_pria' => 'Kartu Keluarga Calon Suami',
            'kk_wanita' => 'Kartu Keluarga Calon Istri',
            'akta_lahir_pria' => 'Akta Lahir Calon Suami',
            'akta_lahir_wanita' => 'Akta Lahir Calon Istri',
            'ijazah_pria' => 'Ijazah Calon Suami',
            'ijazah_wanita' => 'Ijazah Calon Istri',
            'surat_n1' => 'Surat Keterangan Nikah (N1)',
            'surat_n2' => 'Surat Keterangan Asal-Usul (N2)',
            'surat_n4' => 'Surat Persetujuan Mempelai (N4)',
            'foto_pria' => 'Pas Foto Calon Suami',
            'foto_wanita' => 'Pas Foto Calon Istri',
            'surat_izin_orang_tua' => 'Surat Izin Orang Tua',
            'akta_cerai' => 'Akta Cerai',
            'surat_kematian' => 'Surat Kematian',
            'lainnya' => 'Dokumen Lainnya',
        ];

        return $labels[$this->jenis] ?? $this->jenis;
    }
}
