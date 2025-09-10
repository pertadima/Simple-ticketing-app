<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceWebMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Force web content type for admin routes
        if ($request->is('admin*')) {
            $request->headers->set('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        }
        
        return $next($request);
    }
}
