<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class LocaleThrottleMiddleware
{
    /**
     * Handle an incoming request.
     * Limit locale switching to 10 requests per minute per IP
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'locale-switch:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => 'Too many locale switch attempts. Try again in ' . $seconds . ' seconds.',
                'retry_after' => $seconds
            ], 429);
        }
        
        RateLimiter::hit($key, 60); // 1 minute window
        
        return $next($request);
    }
}
