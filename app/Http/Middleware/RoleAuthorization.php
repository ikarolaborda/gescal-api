<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  array<string>|string  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '401',
                        'title' => 'Unauthorized',
                        'detail' => 'Authentication required',
                    ],
                ],
            ], 401);
        }

        // If no specific roles required, just check authentication
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        $userRoles = $user->roles()->pluck('slug')->toArray();
        $hasRequiredRole = ! empty(array_intersect($roles, $userRoles));

        if (! $hasRequiredRole) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '403',
                        'title' => 'Forbidden',
                        'detail' => 'You do not have permission to access this resource',
                        'meta' => [
                            'required_roles' => $roles,
                            'user_roles' => $userRoles,
                        ],
                    ],
                ],
            ], 403);
        }

        return $next($request);
    }
}
