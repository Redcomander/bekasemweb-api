<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Imam;
use Illuminate\Http\Request;

class ImamController extends BaseController
{
    /**
     * Display a listing of imams
     */
    public function index(Request $request)
    {
        $query = Imam::with('masjids');

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

        $imams = $query->orderBy('nama')->get();

        return $this->successResponse($imams, 'Daftar imam');
    }

    /**
     * Store a newly created imam
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

        $imam = Imam::create($validated);

        return $this->createdResponse($imam, 'Imam berhasil ditambahkan');
    }

    /**
     * Display the specified imam
     */
    public function show(Imam $imam)
    {
        $imam->load('masjids');
        return $this->successResponse($imam, 'Detail imam');
    }

    /**
     * Update the specified imam
     */
    public function update(Request $request, Imam $imam)
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $imam->update($validated);

        return $this->successResponse($imam->fresh(), 'Imam berhasil diupdate');
    }

    /**
     * Remove the specified imam
     */
    public function destroy(Imam $imam)
    {
        $imam->masjids()->detach();
        $imam->delete();
        return $this->successResponse(null, 'Imam berhasil dihapus');
    }
}
