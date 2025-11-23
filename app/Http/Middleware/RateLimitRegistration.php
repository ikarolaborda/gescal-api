<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitRegistration
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(protected RateLimiter $limiter) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'registration:' . $request->ip();

        if ($this->limiter->tooManyAttempts($key, 5)) {
            $seconds = $this->limiter->availableIn($key);

            return response()->json([
                'errors' => [
                    [
                        'status' => '429',
                        'title' => 'Too Many Requests',
                        'detail' => 'Too many registration attempts. Please try again later.',
                    ],
                ],
            ], 429)->header('Retry-After', $seconds);
        }

        $this->limiter->hit($key, 3600); // 1 hour window

        $response = $next($request);

        return $response;
    }
}
