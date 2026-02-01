<?php

namespace App\Http\Controllers\Api;

use App\Models\MajelisTaklim;
use Illuminate\Http\Request;

class MajelisTaklimController extends BaseController
{
    /**
     * Get majelis taklim list (public)
     */
    public function index(Request $request)
    {
        $query = MajelisTaklim::active()
            ->select(['id', 'judul', 'penceramah', 'tanggal', 'jam_mulai', 'jam_selesai', 'tempat', 'status', 'poster', 'youtube_url']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $majelis = $query->orderBy('tanggal', 'desc')->paginate($request->per_page ?? 10);

        return $this->paginatedResponse($majelis, 'Daftar majelis taklim');
    }

    /**
     * Get majelis detail (public)
     */
    public function show(MajelisTaklim $majelisTaklim)
    {
        if (!$majelisTaklim->is_active) {
            return $this->notFoundResponse('Majelis taklim tidak ditemukan');
        }

        return $this->successResponse($majelisTaklim, 'Detail majelis taklim');
    }

    /**
     * Get upcoming majelis
     */
    public function upcoming()
    {
        $majelis = MajelisTaklim::active()
            ->upcoming()
            ->select(['id', 'judul', 'penceramah', 'tanggal', 'jam_mulai', 'tempat', 'poster'])
            ->take(5)
            ->get();

        return $this->successResponse($majelis, 'Majelis taklim akan datang');
    }

    /**
     * Get archived (past) majelis
     */
    public function arsip(Request $request)
    {
        $majelis = MajelisTaklim::active()
            ->arsip()
            ->select(['id', 'judul', 'penceramah', 'tanggal', 'youtube_url'])
            ->paginate($request->per_page ?? 10);

        return $this->paginatedResponse($majelis, 'Arsip majelis taklim');
    }
}
