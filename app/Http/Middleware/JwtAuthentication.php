<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthentication
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Attempt to authenticate user from JWT token
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'errors' => [
                        [
                            'status' => '401',
                            'title' => 'Unauthorized',
                            'detail' => 'User not found',
                        ],
                    ],
                ], 401);
            }

            // Set authenticated user
            auth()->setUser($user);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '401',
                        'title' => 'Token Expired',
                        'detail' => 'The authentication token has expired',
                        'meta' => [
                            'refresh_available' => true,
                        ],
                    ],
                ],
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '401',
                        'title' => 'Token Invalid',
                        'detail' => 'The authentication token is invalid',
                    ],
                ],
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '401',
                        'title' => 'Token Not Provided',
                        'detail' => 'Authorization token is required',
                    ],
                ],
            ], 401);
        }

        return $next($request);
    }
}
