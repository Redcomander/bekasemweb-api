<?php

namespace App\Http\Controllers\Api;

use App\Models\JadwalNikah;
use Illuminate\Http\Request;

class JadwalNikahController extends BaseController
{
    /**
     * Get jadwal nikah list (public)
     */
    public function index(Request $request)
    {
        $query = JadwalNikah::with(['penghulu:id,name'])
            ->select(['id', 'tanggal', 'jam_mulai', 'jam_selesai', 'lokasi', 'status', 'penghulu_id']);

        // Filter by date range
        if ($request->has('from')) {
            $query->where('tanggal', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('tanggal', '<=', $request->to);
        }

        // Default: upcoming only
        if (!$request->has('from') && !$request->has('to')) {
            $query->upcoming();
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $jadwals = $query->orderBy('tanggal')->orderBy('jam_mulai')->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($jadwals, 'Daftar jadwal nikah');
    }

    /**
     * Get available slots for booking
     */
    public function available(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|after:today',
        ]);

        $jadwals = JadwalNikah::available()
            ->onDate($request->tanggal)
            ->select(['id', 'tanggal', 'jam_mulai', 'jam_selesai', 'lokasi'])
            ->orderBy('jam_mulai')
            ->get();

        return $this->successResponse($jadwals, 'Slot tersedia');
    }

    /**
     * Get jadwal for specific month (calendar view)
     */
    public function calendar(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $jadwals = JadwalNikah::whereYear('tanggal', $request->year)
            ->whereMonth('tanggal', $request->month)
            ->whereIn('status', ['booked', 'completed'])
            ->select(['id', 'tanggal', 'jam_mulai', 'lokasi', 'status'])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('tanggal');

        return $this->successResponse($jadwals, 'Jadwal bulan ' . $request->month . '/' . $request->year);
    }
}
