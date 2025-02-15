<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if (
            $currentUser->role_id !== getAdminRoleId()
        ) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}
