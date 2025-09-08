<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SwaggerMiddleware
{
    /**
     * Handle an incoming request for Swagger documentation.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Increase execution time for Swagger documentation generation
        set_time_limit(120); // 2 minutes
        ini_set('memory_limit', '256M');
        
        return $next($request);
    }
}