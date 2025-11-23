<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $organizationId = $request->route('org') ?? $request->route('org_id');

        // If no organization ID in route, skip check
        if (! $organizationId) {
            return $next($request);
        }

        // Verify user's organization matches the requested organization
        if ($user->organization_id != $organizationId) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '403',
                        'title' => 'Forbidden',
                        'detail' => 'You do not have permission to access this organization.',
                    ],
                ],
            ], 403);
        }

        return $next($request);
    }
}
