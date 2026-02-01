<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\KategoriBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KategoriBeritaController extends BaseController
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        $query = KategoriBerita::query();

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        $kategoris = $query->orderBy('nama')->get();

        return $this->successResponse($kategoris, 'Daftar kategori berita');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50|unique:kategori_beritas,nama',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['nama']);

        $kategori = KategoriBerita::create($validated);

        return $this->createdResponse($kategori, 'Kategori berhasil ditambahkan');
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, KategoriBerita $kategoriBerita)
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:50|unique:kategori_beritas,nama,' . $kategoriBerita->id,
            'deskripsi' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['nama'])) {
            $validated['slug'] = Str::slug($validated['nama']);
        }

        $kategoriBerita->update($validated);

        return $this->successResponse($kategoriBerita->fresh(), 'Kategori berhasil diupdate');
    }

    /**
     * Remove the specified category
     */
    public function destroy(KategoriBerita $kategoriBerita)
    {
        $kategoriBerita->delete();
        return $this->successResponse(null, 'Kategori berhasil dihapus');
    }
}
