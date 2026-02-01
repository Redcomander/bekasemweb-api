<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Khotib;
use Illuminate\Http\Request;

class KhotibController extends BaseController
{
    /**
     * Display a listing of khotibs
     */
    public function index(Request $request)
    {
        $query = Khotib::with('masjids');

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $khotibs = $query->orderBy('nama')->get();

        return $this->successResponse($khotibs, 'Daftar khotib');
    }

    /**
     * Store a newly created khotib
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $khotib = Khotib::create($validated);

        return $this->createdResponse($khotib, 'Khotib berhasil ditambahkan');
    }

    /**
     * Display the specified khotib
     */
    public function show(Khotib $khotib)
    {
        $khotib->load('masjids');
        return $this->successResponse($khotib, 'Detail khotib');
    }

    /**
     * Update the specified khotib
     */
    public function update(Request $request, Khotib $khotib)
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $khotib->update($validated);

        return $this->successResponse($khotib->fresh(), 'Khotib berhasil diupdate');
    }

    /**
     * Remove the specified khotib
     */
    public function destroy(Khotib $khotib)
    {
        $khotib->masjids()->detach();
        $khotib->delete();
        return $this->successResponse(null, 'Khotib berhasil dihapus');
    }
}
