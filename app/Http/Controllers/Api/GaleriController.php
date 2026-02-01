<?php

namespace App\Http\Controllers\Api;

use App\Models\Galeri;
use Illuminate\Http\Request;

class GaleriController extends BaseController
{
    /**
     * Get galeri list (public)
     */
    public function index(Request $request)
    {
        $query = Galeri::active()
            ->select(['id', 'judul', 'tipe', 'file_path', 'thumbnail', 'kategori', 'tanggal_kegiatan']);

        // Filter by tipe
        if ($request->has('tipe')) {
            $query->ofType($request->tipe);
        }

        // Filter by kategori
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Featured only
        if ($request->boolean('featured')) {
            $query->featured();
        }

        $galeris = $query->latest()->paginate($request->per_page ?? 12);

        return $this->paginatedResponse($galeris, 'Daftar galeri');
    }

    /**
     * Get galeri detail (public)
     */
    public function show(Galeri $galeri)
    {
        if (!$galeri->is_active) {
            return $this->notFoundResponse('Galeri tidak ditemukan');
        }

        return $this->successResponse($galeri, 'Detail galeri');
    }

    /**
     * Get featured galeri
     */
    public function featured()
    {
        $galeris = Galeri::active()
            ->featured()
            ->select(['id', 'judul', 'tipe', 'file_path', 'thumbnail'])
            ->latest()
            ->take(8)
            ->get();

        return $this->successResponse($galeris, 'Galeri unggulan');
    }
}
