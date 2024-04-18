<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VfFrameGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // set iframe headers
        $response = $next($request);
        $response->headers->set('X-Frame-Options', 'ALLOW-FROM https://vereinsflieger.de');
        $response->headers->set('Content-Security-Policy', 'frame-ancestors https://vereinsflieger.de');

        return $response;
    }
}
