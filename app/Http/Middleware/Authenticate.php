<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // If the request is for admin routes, redirect to Filament login
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('filament.admin.auth.login');
            }
            
            // Default redirect for other routes
            return route('login');
        }
        
        return null;
    }
}
