<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    /**
     * Login dengan username dan password
     * 
     * POST /api/admin/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        // Load roles
        $user->load('roles');

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login berhasil');
    }

    /**
     * Logout - revoke current token
     * 
     * POST /api/admin/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil');
    }

    /**
     * Get authenticated user info
     * 
     * GET /api/admin/me
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('roles');

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $this->getUserPermissions($user),
            'unread_notifications' => $user->unreadNotificationsCount(),
        ], 'User info');
    }

    /**
     * Get user permissions from roles
     */
    private function getUserPermissions(User $user): array
    {
        $permissions = [];
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission->name;
            }
        }
        return array_unique($permissions);
    }
}
