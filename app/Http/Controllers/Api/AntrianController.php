<?php

namespace App\Http\Controllers\Api;

use App\Models\Antrian;
use Illuminate\Http\Request;

class AntrianController extends BaseController
{
    /**
     * Ambil nomor antrian (public)
     */
    public function ambil(Request $request)
    {
        $validated = $request->validate([
            'layanan' => 'required|in:pendaftaran_nikah,konsultasi,legalisir,bimwin,lainnya',
            'nama' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:15',
            'keperluan' => 'nullable|string|max:500',
        ]);

        $validated['tanggal'] = now()->toDateString();

        $antrian = Antrian::create($validated);

        return $this->createdResponse([
            'kode_antrian' => $antrian->kode_antrian,
            'nomor_urut' => $antrian->nomor_urut,
            'layanan' => $antrian->layanan_label,
            'tanggal' => $antrian->tanggal->format('d F Y'),
            'status' => 'Menunggu',
        ], 'Nomor antrian berhasil diambil');
    }

    /**
     * Cek status antrian (public)
     */
    public function status(string $kode)
    {
        $antrian = Antrian::where('kode_antrian', $kode)->first();

        if (!$antrian) {
            return $this->notFoundResponse('Kode antrian tidak ditemukan');
        }

        // Get current serving number
        $currentServing = Antrian::where('tanggal', $antrian->tanggal)
            ->where('layanan', $antrian->layanan)
            ->where('status', 'serving')
            ->first();

        // Count people ahead
        $ahead = Antrian::where('tanggal', $antrian->tanggal)
            ->where('layanan', $antrian->layanan)
            ->where('status', 'waiting')
            ->where('nomor_urut', '<', $antrian->nomor_urut)
            ->count();

        return $this->successResponse([
            'kode_antrian' => $antrian->kode_antrian,
            'nomor_urut' => $antrian->nomor_urut,
            'layanan' => $antrian->layanan_label,
            'status' => $antrian->status,
            'currently_serving' => $currentServing ? $currentServing->nomor_urut : null,
            'people_ahead' => $ahead,
        ], 'Status antrian');
    }

    /**
     * Get current queue display (public, for display screen)
     */
    public function display(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->toDateString());

        $queues = [];
        $layanans = ['pendaftaran_nikah', 'konsultasi', 'legalisir', 'bimwin', 'lainnya'];

        foreach ($layanans as $layanan) {
            $serving = Antrian::where('tanggal', $tanggal)
                ->where('layanan', $layanan)
                ->where('status', 'serving')
                ->first();

            $waiting = Antrian::where('tanggal', $tanggal)
                ->where('layanan', $layanan)
                ->where('status', 'waiting')
                ->count();

            $queues[] = [
                'layanan' => $layanan,
                'label' => Antrian::make(['layanan' => $layanan])->layanan_label,
                'current' => $serving ? $serving->kode_antrian : '-',
                'waiting' => $waiting,
            ];
        }

        return $this->successResponse($queues, 'Display antrian');
    }
}
