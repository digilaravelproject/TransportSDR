<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleCheckMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized for this action.'], 403);
        }
        return $next($request);
    }
}
