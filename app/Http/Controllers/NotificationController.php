<?php

namespace App\Http\Controllers;

use App\Models\FarmerNotification;
use App\Models\DeliveryBoyNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Handle based on user role
        if ($user->role === 'delivery_boy') {
            $notifications = DeliveryBoyNotification::where('user_id', $user->id)
                ->latest('sent_at')
                ->get()
                ->map(function ($notification) {
                    return $this->formatDeliveryBoyNotification($notification);
                });
        } else {
            // Default to farmer notifications
            $notifications = FarmerNotification::where('user_id', $user->id)
                ->latest('sent_at')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function markRead(Request $request, $notificationId)
    {
        $user = $request->user();
        
        if ($user->role === 'delivery_boy') {
            $notification = DeliveryBoyNotification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found.',
                ], 404);
            }
        } else {
            $notification = FarmerNotification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found.',
                ], 404);
            }
        }

        $notification->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data' => $user->role === 'delivery_boy' 
                ? $this->formatDeliveryBoyNotification($notification)
                : $notification,
        ]);
    }

    /**
     * Format delivery boy notification with relative time
     */
    private function formatDeliveryBoyNotification($notification)
    {
        $sentAt = $notification->sent_at ?? $notification->created_at;
        $relativeTime = $this->getRelativeTime($sentAt);

        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'body' => $notification->body,
            'type' => $notification->type,
            'icon' => $notification->icon,
            'sent_at' => $sentAt ? $sentAt->format('Y-m-d H:i:s') : null,
            'read_at' => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : null,
            'is_read' => $notification->read_at !== null,
            'relative_time' => $relativeTime, // e.g., "10 min ago", "1 hour ago", "Yesterday"
        ];
    }

    /**
     * Get relative time string (e.g., "10 min ago", "1 hour ago", "Yesterday")
     */
    private function getRelativeTime($datetime)
    {
        if (!$datetime) {
            return null;
        }

        $now = Carbon::now();
        $diff = $now->diffInMinutes($datetime);

        if ($diff < 1) {
            return 'Just now';
        } elseif ($diff < 60) {
            return $diff . ' min ago';
        } elseif ($diff < 1440) { // Less than 24 hours
            $hours = floor($diff / 60);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2880) { // Less than 48 hours
            return 'Yesterday';
        } else {
            $days = floor($diff / 1440);
            if ($days < 7) {
                return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
            } else {
                return $datetime->format('M d, Y');
            }
        }
    }
}


