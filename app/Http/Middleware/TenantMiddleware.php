<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }
        if ($user->role === 'superadmin') return $next($request);

        if (!$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'No tenant assigned.'], 403);
        }
        $tenant = $user->tenant;
        if (!$tenant || !$tenant->isActive()) {
            return response()->json(['success' => false, 'message' => 'Subscription expired. Contact support.'], 403);
        }
        if (!$user->is_active) {
            return response()->json(['success' => false, 'message' => 'Account disabled.'], 403);
        }
        return $next($request);
    }
}
