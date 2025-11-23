<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonApiHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enforce JSON:API Content-Type for requests with body
        if ($request->isMethod('POST') || $request->isMethod('PATCH') || $request->isMethod('PUT')) {
            $contentType = $request->header('Content-Type');

            if ($contentType !== 'application/vnd.api+json') {
                return response()->json([
                    'errors' => [
                        [
                            'status' => '415',
                            'title' => 'Unsupported Media Type',
                            'detail' => 'Request Content-Type must be application/vnd.api+json',
                            'source' => [
                                'header' => 'Content-Type',
                            ],
                        ],
                    ],
                ], 415);
            }
        }

        // Enforce JSON:API Accept header
        $accept = $request->header('Accept');
        if ($accept && $accept !== '*/*' && ! str_contains($accept, 'application/vnd.api+json')) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '406',
                        'title' => 'Not Acceptable',
                        'detail' => 'Accept header must include application/vnd.api+json',
                        'source' => [
                            'header' => 'Accept',
                        ],
                    ],
                ],
            ], 406);
        }

        $response = $next($request);

        // Set JSON:API Content-Type on response
        if ($response instanceof Response) {
            $response->headers->set('Content-Type', 'application/vnd.api+json');
        }

        return $response;
    }
}
