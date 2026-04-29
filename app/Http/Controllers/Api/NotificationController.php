<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/v1/notifications
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $perPage = (int) ($request->per_page ?? 20);

        $query = Notification::where('tenant_id', $tenantId)->orderBy('created_at', 'desc');

        if ($request->filled('unread_only') && $request->unread_only) {
            $query->where('is_read', false);
        }

        $page = $query->paginate($perPage);

        return response()->json(['success' => true, 'data' => $page->items(), 'meta' => ['total' => $page->total(), 'current_page' => $page->currentPage()]]);
    }
}
