<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerOrAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $requestedUserId = optional($request->route('user'))->id;
        $requestedUserWishlistId = optional($request->route('wishlist'))->user_id;

        if (
            $currentUser->role_id === (int) getAdminRoleId() ||
            $currentUser->id === (int) $requestedUserId ||
            $currentUser->id === (int) $requestedUserWishlistId
        ) {
            return $next($request);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}
