<?php

namespace App\Http\Controllers\Api;

use App\Models\PendaftaranNikah;
use App\Models\DokumenNikah;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendaftaranNikahController extends BaseController
{
    /**
     * Submit pendaftaran nikah (public)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Data Pria
            'nama_pria' => 'required|string|max:255',
            'nik_pria' => 'required|string|size:16',
            'tempat_lahir_pria' => 'required|string|max:100',
            'tanggal_lahir_pria' => 'required|date|before:-17 years',
            'alamat_pria' => 'required|string',
            'no_hp_pria' => 'required|string|max:15',
            'pekerjaan_pria' => 'nullable|string|max:100',
            'status_pria' => 'required|in:jejaka,duda',

            // Data Wanita
            'nama_wanita' => 'required|string|max:255',
            'nik_wanita' => 'required|string|size:16',
            'tempat_lahir_wanita' => 'required|string|max:100',
            'tanggal_lahir_wanita' => 'required|date|before:-17 years',
            'alamat_wanita' => 'required|string',
            'no_hp_wanita' => 'required|string|max:15',
            'pekerjaan_wanita' => 'nullable|string|max:100',
            'status_wanita' => 'required|in:perawan,janda',

            // Data Wali
            'nama_wali' => 'required|string|max:255',
            'hubungan_wali' => 'required|string|max:100',
            'no_hp_wali' => 'nullable|string|max:15',

            // Rencana Nikah
            'tanggal_nikah' => 'required|date|after:+10 days',
            'jam_nikah' => 'nullable|date_format:H:i',
            'lokasi_nikah' => 'required|string|max:100',
            'alamat_nikah' => 'nullable|string',
            'mahar' => 'nullable|numeric|min:0',
            'mahar_keterangan' => 'nullable|string|max:255',
        ]);

        $pendaftaran = PendaftaranNikah::create($validated);

        // Notify admin
        $admins = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'admin_kua']);
        })->get();

        foreach ($admins as $admin) {
            Notifikasi::notify(
                $admin->id,
                'Pendaftaran Nikah Baru',
                "Pendaftaran nikah baru dari {$pendaftaran->nama_pria} & {$pendaftaran->nama_wanita}",
                [
                    'tipe' => 'info',
                    'link' => "/admin/pendaftaran/{$pendaftaran->id}",
                    'data' => ['pendaftaran_id' => $pendaftaran->id],
                ]
            );
        }

        return $this->createdResponse([
            'kode_pendaftaran' => $pendaftaran->kode_pendaftaran,
            'status' => $pendaftaran->status,
            'message' => 'Pendaftaran nikah berhasil diajukan. Silakan upload dokumen yang diperlukan.',
        ], 'Pendaftaran berhasil');
    }

    /**
     * Check pendaftaran status by kode (public)
     */
    public function status(string $kode)
    {
        $pendaftaran = PendaftaranNikah::where('kode_pendaftaran', $kode)
            ->select(['id', 'kode_pendaftaran', 'nama_pria', 'nama_wanita', 'tanggal_nikah', 'status', 'catatan_admin', 'created_at'])
            ->first();

        if (!$pendaftaran) {
            return $this->notFoundResponse('Kode pendaftaran tidak ditemukan');
        }

        // Get dokumen status
        $dokumens = DokumenNikah::where('pendaftaran_id', $pendaftaran->id)
            ->select(['id', 'jenis', 'status', 'catatan', 'created_at'])
            ->get()
            ->each(fn($doc) => $doc->append('jenis_label'));

        return $this->successResponse([
            'pendaftaran' => $pendaftaran,
            'dokumens' => $dokumens,
        ], 'Status pendaftaran');
    }

    /**
     * Upload dokumen for pendaftaran (public)
     */
    public function uploadDokumen(Request $request, string $kode)
    {
        $request->validate([
            'jenis' => 'required|in:ktp_pria,ktp_wanita,kk_pria,kk_wanita,akta_lahir_pria,akta_lahir_wanita,ijazah_pria,ijazah_wanita,surat_n1,surat_n2,surat_n4,foto_pria,foto_wanita,surat_izin_orang_tua,akta_cerai,surat_kematian,lainnya',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $pendaftaran = PendaftaranNikah::where('kode_pendaftaran', $kode)->first();

        if (!$pendaftaran) {
            return $this->notFoundResponse('Kode pendaftaran tidak ditemukan');
        }

        // Can only upload if status allows
        if (!in_array($pendaftaran->status, ['diajukan', 'revisi'])) {
            return $this->errorResponse('Tidak dapat mengupload dokumen pada status ini', 400);
        }

        $file = $request->file('file');
        $path = $file->store("pendaftaran/{$pendaftaran->id}", 'public');

        $dokumen = DokumenNikah::updateOrCreate(
            [
                'pendaftaran_id' => $pendaftaran->id,
                'jenis' => $request->jenis,
            ],
            [
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'status' => 'pending',
                'catatan' => null,
            ]
        );

        return $this->successResponse($dokumen, 'Dokumen berhasil diupload');
    }
}
