<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->role !== 'superadmin') {
            return response()->json(['success' => false, 'message' => 'Forbidden. Super admin only.'], 403);
        }
        return $next($request);
    }
}
