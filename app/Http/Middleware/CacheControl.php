<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't cache pages if user is authenticated (authentication state changes)
        if (auth()->check()) {
            // For authenticated users, prevent caching to ensure fresh auth state
            if ($request->is('products*') || $request->is('/')) {
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
        } else {
            // For guest users, cache public pages
            if ($request->is('products*') || $request->is('/')) {
                $response->headers->set('Cache-Control', 'public, max-age=300, s-maxage=300');
            }
        }

        // Always cache static assets
        if ($request->is('assets/*') || $request->is('build/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        return $response;
    }
}


