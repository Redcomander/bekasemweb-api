<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\PendaftaranNikah;
use App\Models\DokumenNikah;
use App\Models\JadwalNikah;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;

class PendaftaranNikahController extends BaseController
{
    /**
     * List all pendaftaran nikah with filters
     */
    public function index(Request $request)
    {
        $query = PendaftaranNikah::with(['penghulu:id,name', 'jadwal:id,pendaftaran_id,tanggal,jam_mulai,lokasi,status'])
            ->select([
                'id',
                'kode_pendaftaran',
                'nama_pria',
                'nama_wanita',
                'tanggal_nikah',
                'lokasi_nikah',
                'status',
                'penghulu_id',
                'created_at',
                'verified_at'
            ]);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('tanggal_nikah', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('tanggal_nikah', '<=', $request->to);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_pendaftaran', 'like', "%{$search}%")
                    ->orWhere('nama_pria', 'like', "%{$search}%")
                    ->orWhere('nama_wanita', 'like', "%{$search}%");
            });
        }

        $pendaftaran = $query->latest()->paginate($request->per_page ?? 20);

        return $this->paginatedResponse($pendaftaran, 'Daftar pendaftaran nikah');
    }

    /**
     * Get detail pendaftaran with documents
     */
    public function show($id)
    {
        $pendaftaran = PendaftaranNikah::with([
            'penghulu:id,name',
            'verifier:id,name',
            'dokumens',
            'jadwal'
        ])->find($id);

        if (!$pendaftaran) {
            return $this->notFoundResponse('Pendaftaran tidak ditemukan');
        }

        return $this->successResponse($pendaftaran, 'Detail pendaftaran');
    }

    /**
     * Update pendaftaran status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:verifikasi,disetujui,revisi,ditolak,selesai',
            'catatan' => 'nullable|string|max:500',
        ]);

        $pendaftaran = PendaftaranNikah::find($id);

        if (!$pendaftaran) {
            return $this->notFoundResponse('Pendaftaran tidak ditemukan');
        }

        $pendaftaran->update([
            'status' => $request->status,
            'catatan_admin' => $request->catatan,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        // Create notification for status change (could notify via SMS/email in future)
        // For now, just log

        return $this->successResponse([
            'id' => $pendaftaran->id,
            'status' => $pendaftaran->status,
        ], 'Status berhasil diperbarui');
    }

    /**
     * Assign penghulu to pendaftaran
     */
    public function assignPenghulu(Request $request, $id)
    {
        $request->validate([
            'penghulu_id' => 'required|exists:users,id',
        ]);

        $pendaftaran = PendaftaranNikah::find($id);

        if (!$pendaftaran) {
            return $this->notFoundResponse('Pendaftaran tidak ditemukan');
        }

        $pendaftaran->update([
            'penghulu_id' => $request->penghulu_id,
        ]);

        // Notify penghulu
        Notifikasi::notify(
            $request->penghulu_id,
            'Tugas Penghulu Baru',
            "Anda ditugaskan untuk prosesi nikah {$pendaftaran->nama_pria} & {$pendaftaran->nama_wanita}",
            [
                'tipe' => 'info',
                'link' => "/admin/pendaftaran/{$pendaftaran->id}",
            ]
        );

        return $this->successResponse([
            'id' => $pendaftaran->id,
            'penghulu_id' => $pendaftaran->penghulu_id,
        ], 'Penghulu berhasil ditugaskan');
    }

    /**
     * Create jadwal nikah from pendaftaran
     */
    public function createJadwal(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date|after:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            'lokasi' => 'required|string|max:255',
            'catatan' => 'nullable|string|max:500',
        ]);

        $pendaftaran = PendaftaranNikah::find($id);

        if (!$pendaftaran) {
            return $this->notFoundResponse('Pendaftaran tidak ditemukan');
        }

        // Check if jadwal already exists
        if ($pendaftaran->jadwal) {
            return $this->errorResponse('Jadwal nikah sudah dibuat untuk pendaftaran ini', 400);
        }

        $jadwal = JadwalNikah::create([
            'pendaftaran_id' => $pendaftaran->id,
            'penghulu_id' => $pendaftaran->penghulu_id,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai ?? date('H:i', strtotime($request->jam_mulai) + 3600),
            'lokasi' => $request->lokasi,
            'catatan' => $request->catatan,
            'status' => 'booked',
        ]);

        // Update pendaftaran tanggal_nikah if different
        if ($pendaftaran->tanggal_nikah != $request->tanggal) {
            $pendaftaran->update(['tanggal_nikah' => $request->tanggal]);
        }

        return $this->createdResponse($jadwal, 'Jadwal nikah berhasil dibuat');
    }

    /**
     * Update document status
     */
    public function updateDokumenStatus(Request $request, $id, $dokumenId)
    {
        $request->validate([
            'status' => 'required|in:pending,valid,invalid',
            'catatan' => 'nullable|string|max:255',
        ]);

        $dokumen = DokumenNikah::where('pendaftaran_id', $id)
            ->where('id', $dokumenId)
            ->first();

        if (!$dokumen) {
            return $this->notFoundResponse('Dokumen tidak ditemukan');
        }

        $dokumen->update([
            'status' => $request->status,
            'catatan' => $request->catatan,
        ]);

        return $this->successResponse($dokumen, 'Status dokumen diperbarui');
    }

    /**
     * Get list of penghulu (for dropdown)
     */
    public function penghulus()
    {
        $penghulus = User::whereHas('roles', function ($q) {
            $q->where('name', 'penghulu');
        })->select(['id', 'name'])->get();

        return $this->successResponse($penghulus, 'Daftar penghulu');
    }
}
