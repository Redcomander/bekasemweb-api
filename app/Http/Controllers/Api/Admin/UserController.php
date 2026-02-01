<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderByDesc('created_at')->paginate($request->per_page ?? 20);

        // Transform to include role names
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign role
        $role = Role::where('name', $validated['role'])->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
            ]
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'username' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|required|string',
        ]);

        // Update basic info
        $updateData = [
            'name' => $validated['name'] ?? $user->name,
            'username' => $validated['username'] ?? $user->username,
            'email' => $validated['email'] ?? $user->email,
        ];

        // Update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update role if provided
        if (isset($validated['role'])) {
            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->fresh()->roles->pluck('name')->toArray(),
            ]
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri'
            ], 403);
        }

        $user->roles()->detach();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }

    /**
     * Get all available roles
     */
    public function roles()
    {
        $roles = Role::select('id', 'name', 'description')->get();
        
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }
}
