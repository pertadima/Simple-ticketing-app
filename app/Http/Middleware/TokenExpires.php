<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\ApiErrorHelper;

class TokenExpires
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $apiErrorHelper = new ApiErrorHelper();
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $expiresAt = $token->created_at->addHours(2); // Token valid for 2 hours
            if (now()->greaterThan($expiresAt)) {
                $token->delete(); // Optionally revoke the token
                return response()->json($apiErrorHelper->formatError(
                    title: 'Token expired',
                    status: 401,
                    detail: 'Please relogin to update token'
                ), 401);
            }
        }

        return $next($request);
    }
}
