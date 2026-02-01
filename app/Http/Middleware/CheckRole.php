<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  One or more role names
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Super admin bypass all role checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki role yang diperlukan.',
            ], 403);
        }

        return $next($request);
    }
}
