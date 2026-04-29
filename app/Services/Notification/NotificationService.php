<?php

namespace App\Services\Notification;

use App\Models\Notification;

class NotificationService
{
    public function create(string $title, ?string $message = null, array $data = [], string $type = 'info', string $priority = 'medium') : Notification
    {
        $user = auth()->user();
        return Notification::create([
            'tenant_id' => $user->tenant_id ?? null,
            'user_id'   => $user->id ?? null,
            'type'      => $type,
            'title'     => $title,
            'message'   => $message,
            'data'      => $data ?: null,
            'priority'  => $priority,
        ]);
    }
}
