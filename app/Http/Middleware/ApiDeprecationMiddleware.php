<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiDeprecationMiddleware
{
    /**
     * Add deprecation headers to API V1 responses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if V1 deprecation is enabled via config
        $v1IsDeprecated = config('api.v1_deprecated', false);

        // Add version headers to V1 routes
        if ($request->is('api/v1/*')) {
            $response->headers->set('X-API-Version', '1.0');

            // Only add deprecation headers if V1 is actually deprecated
            if ($v1IsDeprecated) {
                $sunsetDate = config('api.v1_sunset_date', now()->addYear()->format('D, d M Y H:i:s T'));
                $deprecationDate = config('api.v1_deprecation_date', now()->format('D, d M Y H:i:s T'));

                $response->headers->set('Sunset', $sunsetDate);
                $response->headers->set('Deprecation', $deprecationDate);
                $response->headers->set(
                    'Link',
                    '</api/v2>; rel="successor-version", ' .
                    '<https://docs.example.com/api/migration-guide>; rel="deprecation"'
                );
                $response->headers->set('X-API-Deprecated', 'true');
                $response->headers->set('X-API-Sunset-Info', 'Please migrate to /api/v2 before ' . $sunsetDate);
            } else {
                // V1 is current and active
                $response->headers->set('X-API-Deprecated', 'false');
            }
        }

        // Add version header to V2 routes
        if ($request->is('api/v2/*')) {
            $response->headers->set('X-API-Version', '2.0');
            $response->headers->set('X-API-Deprecated', 'false');
        }

        return $response;
    }
}
