<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = null, string $permission = null): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is an admin user
        if (!$user instanceof \App\Models\AdminUser) {
            abort(403, 'Access denied: Admin access required');
        }

        // Check if user is active
        if (!$user->is_active) {
            abort(403, 'Access denied: Account is inactive');
        }

        // Check specific role if provided
        if ($role && !$user->hasRole($role)) {
            abort(403, "Access denied: {$role} role required");
        }

        // Check specific permission if provided
        if ($permission && !$user->hasPermission($permission)) {
            abort(403, "Access denied: {$permission} permission required");
        }

        return $next($request);
    }
}
