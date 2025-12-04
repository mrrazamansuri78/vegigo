<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\DeliveryBoyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryBoyController extends Controller
{
    /**
     * Get delivery boy dashboard data
     * Includes: Today's run info, Nearby pickup requests, Active deliveries
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // Ensure user is delivery boy
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only delivery boys can access this endpoint.',
            ], 403);
        }

        // Get or create delivery boy profile
        $profile = DeliveryBoyProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_location_city' => 'Bangalore',
                'current_location_area' => 'Indiranagar',
                'current_speed_kmh' => 0,
                'battery_percentage' => 100,
                'is_on_route' => false,
            ]
        );

        // Get nearby pickup requests (orders with status 'ready' and no delivery_boy_id assigned)
        $nearbyPickups = Order::where('status', 'ready')
            ->whereNull('delivery_boy_id')
            ->with('farmer:id,name,phone')
            ->orderBy('distance_km', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'distance_km' => $order->distance_km ?? 0,
                    'customer_name' => $order->customer_name,
                    'pickup_address' => $order->pickup_address,
                    'items' => $order->items,
                    'farmer' => $order->farmer ? [
                        'name' => $order->farmer->name,
                        'phone' => $order->farmer->phone,
                    ] : null,
                    'ready_date' => $order->ready_date?->format('Y-m-d'),
                ];
            });

        // Get active deliveries (orders assigned to this delivery boy with status 'accepted' or 'picked_up')
        $activeDeliveries = Order::where('delivery_boy_id', $user->id)
            ->whereIn('status', ['accepted', 'picked_up'])
            ->with('farmer:id,name,phone')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $totalQuantity = 0;
                $itemCount = count($order->items ?? []);
                
                // Calculate total quantity from items
                foreach ($order->items ?? [] as $item) {
                    if (isset($item['quantity'])) {
                        // Extract number from "120.0 kg" format
                        preg_match('/(\d+\.?\d*)/', $item['quantity'], $matches);
                        $totalQuantity += floatval($matches[1] ?? 0);
                    }
                }

                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'status' => strtoupper($order->status),
                    'customer_name' => $order->customer_name,
                    'pickup_address' => $order->pickup_address,
                    'drop_address' => $order->drop_address,
                    'items' => $order->items,
                    'item_count' => $itemCount,
                    'total_quantity' => $totalQuantity,
                    'unit' => 'kg', // Default unit, can be made dynamic
                    'farmer' => $order->farmer ? [
                        'name' => $order->farmer->name,
                        'phone' => $order->farmer->phone,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'todays_run' => [
                    'location' => $profile->current_location_area . ', ' . $profile->current_location_city,
                    'speed_kmh' => $profile->current_speed_kmh,
                    'battery_percentage' => $profile->battery_percentage,
                    'is_on_route' => $profile->is_on_route,
                ],
                'nearby_pickup_requests' => $nearbyPickups,
                'active_deliveries' => $activeDeliveries,
            ],
        ]);
    }

    /**
     * Accept a pickup request (assign order to delivery boy)
     */
    public function acceptPickup(Request $request, $orderId)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $order = Order::where('id', $orderId)
            ->where('status', 'ready')
            ->whereNull('delivery_boy_id')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or already assigned.',
            ], 404);
        }

        $order->update([
            'delivery_boy_id' => $user->id,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        // Update delivery boy profile to show on route
        DeliveryBoyProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['is_on_route' => true]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pickup request accepted successfully.',
            'data' => $order->load('farmer:id,name,phone'),
        ]);
    }

    /**
     * Reject a pickup request
     * For now, we just return success. In future, you might want to track rejections.
     */
    public function rejectPickup(Request $request, $orderId)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Just return success - order remains available for other delivery boys
        return response()->json([
            'success' => true,
            'message' => 'Pickup request rejected.',
        ]);
    }

    /**
     * Mark delivery as completed
     */
    public function markDelivered(Request $request, $orderId)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $order = Order::where('id', $orderId)
            ->where('delivery_boy_id', $user->id)
            ->where('status', 'picked_up')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not in correct status.',
            ], 404);
        }

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Check if there are more active deliveries
        $hasMoreDeliveries = Order::where('delivery_boy_id', $user->id)
            ->whereIn('status', ['accepted', 'picked_up'])
            ->exists();

        // If no more active deliveries, update profile
        if (!$hasMoreDeliveries) {
            DeliveryBoyProfile::updateOrCreate(
                ['user_id' => $user->id],
                ['is_on_route' => false]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery marked as completed.',
            'data' => $order,
        ]);
    }

    /**
     * Update delivery boy location, speed, battery
     */
    public function updateLocation(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $data = $request->validate([
            'current_location_city' => ['nullable', 'string', 'max:255'],
            'current_location_area' => ['nullable', 'string', 'max:255'],
            'current_latitude' => ['nullable', 'numeric'],
            'current_longitude' => ['nullable', 'numeric'],
            'current_speed_kmh' => ['nullable', 'numeric', 'min:0'],
            'battery_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_on_route' => ['nullable', 'boolean'],
        ]);

        $profile = DeliveryBoyProfile::firstOrCreate(['user_id' => $user->id]);
        $profile->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully.',
            'data' => $profile,
        ]);
    }

    /**
     * Track current active delivery
     * Returns live route sync info, delivery status, and timeline
     */
    public function track(Request $request, $orderId = null)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // If orderId not provided, get the most recent active delivery
        if (!$orderId) {
            $order = Order::where('delivery_boy_id', $user->id)
                ->whereIn('status', ['accepted', 'picked_up'])
                ->orderBy('created_at', 'desc')
                ->first();
        } else {
            $order = Order::where('id', $orderId)
                ->where('delivery_boy_id', $user->id)
                ->first();
        }

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'No active delivery found.',
            ], 404);
        }

        // Get delivery boy profile for location/speed/battery
        $profile = DeliveryBoyProfile::firstOrCreate(['user_id' => $user->id]);

        // Build timeline
        $timeline = [];
        
        if ($order->created_at) {
            $timeline[] = [
                'event' => 'Order Created',
                'status' => 'created',
                'timestamp' => $order->created_at->format('Y-m-d H:i:s'),
                'formatted_time' => $order->created_at->format('H:i') . ' • ' . $order->created_at->format('d/m/Y'),
            ];
        }

        if ($order->accepted_at) {
            $timeline[] = [
                'event' => 'Order Accepted',
                'status' => 'accepted',
                'timestamp' => $order->accepted_at->format('Y-m-d H:i:s'),
                'formatted_time' => $order->accepted_at->format('H:i') . ' • ' . $order->accepted_at->format('d/m/Y'),
            ];
        }

        if ($order->picked_up_at) {
            $timeline[] = [
                'event' => 'Picked Up',
                'status' => 'picked_up',
                'timestamp' => $order->picked_up_at->format('Y-m-d H:i:s'),
                'formatted_time' => $order->picked_up_at->format('H:i') . ' • ' . $order->picked_up_at->format('d/m/Y'),
            ];
        }

        // Add pending delivery if not delivered yet
        if ($order->status !== 'delivered') {
            $timeline[] = [
                'event' => 'Delivery Pending',
                'status' => 'pending',
                'timestamp' => null,
                'formatted_time' => null,
            ];
        }

        if ($order->delivered_at) {
            $timeline[] = [
                'event' => 'Delivered',
                'status' => 'delivered',
                'timestamp' => $order->delivered_at->format('Y-m-d H:i:s'),
                'formatted_time' => $order->delivered_at->format('H:i') . ' • ' . $order->delivered_at->format('d/m/Y'),
            ];
        }

        // Calculate total quantity
        $totalQuantity = 0;
        $itemCount = count($order->items ?? []);
        $unit = 'kg';
        
        foreach ($order->items ?? [] as $item) {
            if (isset($item['quantity'])) {
                preg_match('/(\d+\.?\d*)\s*([a-zA-Z]+)?/', $item['quantity'], $matches);
                $totalQuantity += floatval($matches[1] ?? 0);
                if (isset($matches[2])) {
                    $unit = $matches[2]; // Use unit from first item
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'status' => strtoupper($order->status),
                    'customer_name' => $order->customer_name,
                    'pickup_address' => $order->pickup_address,
                    'drop_address' => $order->drop_address,
                    'landmark' => $order->landmark,
                    'distance_km' => $order->distance_km,
                    'items' => $order->items,
                    'item_count' => $itemCount,
                    'total_quantity' => $totalQuantity,
                    'unit' => $unit,
                ],
                'live_route_sync' => [
                    'signal_locked' => true, // Can be made dynamic based on GPS
                    'status' => strtoupper($order->status),
                ],
                'locations' => [
                    'pickup' => [
                        'city' => explode(',', $order->pickup_address ?? 'Bangalore')[0] ?? 'Bangalore',
                        'address' => $order->pickup_address,
                    ],
                    'drop' => [
                        'city' => explode(',', $order->drop_address ?? 'Bangalore')[0] ?? 'Bangalore',
                        'address' => $order->drop_address,
                        'contact_person' => $order->drop_contact_person,
                        'contact_phone' => $order->drop_contact_phone,
                    ],
                ],
                'delivery_status' => [
                    'current_status' => $order->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
                    'message' => $this->getStatusMessage($order->status),
                ],
                'order_timeline' => $timeline,
                'delivery_boy_location' => [
                    'location' => $profile->current_location_area . ', ' . $profile->current_location_city,
                    'speed_kmh' => $profile->current_speed_kmh,
                    'battery_percentage' => $profile->battery_percentage,
                ],
            ],
        ]);
    }

    /**
     * Get status message based on order status
     */
    private function getStatusMessage($status)
    {
        $messages = [
            'accepted' => 'Order accepted. Proceed to pickup location.',
            'picked_up' => 'Items picked up. Proceed to drop location.',
            'delivered' => 'Order delivered successfully.',
            'pending' => 'Waiting for pickup.',
        ];

        return $messages[$status] ?? 'Order in progress.';
    }

    /**
     * Mark order as picked up
     */
    public function markPickedUp(Request $request, $orderId)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $order = Order::where('id', $orderId)
            ->where('delivery_boy_id', $user->id)
            ->whereIn('status', ['accepted'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not in correct status.',
            ], 404);
        }

        $order->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        // Update delivery boy profile to show on route
        DeliveryBoyProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['is_on_route' => true]
        );

        return response()->json([
            'success' => true,
            'message' => 'Order marked as picked up.',
            'data' => $order,
        ]);
    }

    /**
     * Get delivery history (list of delivered orders)
     */
    public function history(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $orders = Order::where('delivery_boy_id', $user->id)
            ->where('status', 'delivered')
            ->with('farmer:id,name,phone')
            ->orderBy('delivered_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Calculate totals
                $totalQuantity = 0;
                $itemCount = count($order->items ?? []);
                $unit = 'kg';
                
                foreach ($order->items ?? [] as $item) {
                    if (isset($item['quantity'])) {
                        preg_match('/(\d+\.?\d*)\s*([a-zA-Z]+)?/', $item['quantity'], $matches);
                        $totalQuantity += floatval($matches[1] ?? 0);
                        if (isset($matches[2])) {
                            $unit = $matches[2];
                        }
                    }
                }

                // Extract city from addresses
                $pickupCity = 'Bangalore';
                $dropCity = 'Bangalore';
                
                if ($order->pickup_address) {
                    $parts = explode(',', $order->pickup_address);
                    $pickupCity = trim(end($parts)) ?: 'Bangalore';
                }
                
                if ($order->drop_address) {
                    $parts = explode(',', $order->drop_address);
                    $dropCity = trim(end($parts)) ?: 'Bangalore';
                }

                // Truncate drop address for display
                $dropAddressShort = $order->drop_address;
                if (strlen($dropAddressShort) > 20) {
                    $dropAddressShort = substr($dropAddressShort, 0, 20) . '...';
                }

                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'customer_name' => $order->customer_name,
                    'status' => strtoupper($order->status),
                    'delivery_date' => $order->delivered_at ? $order->delivered_at->format('M d') : null,
                    'delivery_date_full' => $order->delivered_at ? $order->delivered_at->format('Y-m-d') : null,
                    'item_count' => $itemCount,
                    'total_quantity' => $totalQuantity,
                    'unit' => $unit,
                    'distance_km' => $order->distance_km ?? 0,
                    'pickup_city' => $pickupCity,
                    'drop_address_short' => $dropAddressShort,
                    'drop_address' => $order->drop_address,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get order details (Full Details screen)
     */
    public function orderDetails(Request $request, $orderId)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $order = Order::where('id', $orderId)
            ->where('delivery_boy_id', $user->id)
            ->with('farmer:id,name,phone')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Calculate totals
        $totalQuantity = 0;
        $itemCount = count($order->items ?? []);
        $unit = 'kg';
        
        foreach ($order->items ?? [] as $item) {
            if (isset($item['quantity'])) {
                preg_match('/(\d+\.?\d*)\s*([a-zA-Z]+)?/', $item['quantity'], $matches);
                $totalQuantity += floatval($matches[1] ?? 0);
                if (isset($matches[2])) {
                    $unit = $matches[2];
                }
            }
        }

        // Build timeline
        $timeline = [];
        
        if ($order->created_at) {
            $timeline[] = [
                'event' => 'Order Created',
                'status' => 'created',
                'timestamp' => $order->created_at->format('Y-m-d H:i:s'),
                'formatted_time' => $order->created_at->format('M d, Y • h:i A'),
            ];
        }

        if ($order->accepted_at) {
            $timeline[] = [
                'event' => 'Order Accepted',
                'status' => 'accepted',
                'timestamp' => $order->accepted_at->format('Y-m-d H:i:s'),
                'formatted_time' => $order->accepted_at->format('M d, Y • h:i A'),
            ];
        }

        if ($order->picked_up_at) {
            $timeline[] = [
                'event' => 'Picked Up from Farmer',
                'status' => 'picked_up',
                'timestamp' => $order->picked_up_at->format('Y-m-d H:i:s'),
                'formatted_time' => $order->picked_up_at->format('M d, Y • h:i A'),
            ];
        }

        if ($order->status !== 'delivered') {
            $timeline[] = [
                'event' => 'Pending Delivery',
                'status' => 'pending',
                'timestamp' => null,
                'formatted_time' => null,
            ];
        }

        if ($order->delivered_at) {
            $timeline[] = [
                'event' => 'Delivered Successfully',
                'status' => 'delivered',
                'timestamp' => $order->delivered_at->format('Y-m-d H:i:s'),
                'formatted_time' => $order->delivered_at->format('M d, Y • h:i A'),
            ];
        }

        // Calculate delivery summary times
        $deliverySummary = null;
        if ($order->accepted_at && $order->picked_up_at && $order->delivered_at) {
            $acceptanceToPickup = $order->accepted_at->diffInMinutes($order->picked_up_at);
            $pickupToDelivery = $order->picked_up_at->diffInMinutes($order->delivered_at);
            $totalTime = $order->accepted_at->diffInMinutes($order->delivered_at);

            $deliverySummary = [
                'acceptance_to_pickup_minutes' => $acceptanceToPickup,
                'acceptance_to_pickup_formatted' => $this->formatDuration($acceptanceToPickup),
                'pickup_to_delivery_minutes' => $pickupToDelivery,
                'pickup_to_delivery_formatted' => $this->formatDuration($pickupToDelivery),
                'total_time_minutes' => $totalTime,
                'total_time_formatted' => $this->formatDuration($totalTime),
                'distance_km' => $order->distance_km ?? 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'status' => strtoupper($order->status),
                    'customer_name' => $order->customer_name,
                ],
                'summary' => [
                    'item_count' => $itemCount,
                    'total_quantity' => $totalQuantity,
                    'unit' => $unit,
                    'distance_km' => $order->distance_km,
                ],
                'farmer' => $order->farmer ? [
                    'name' => $order->farmer->name,
                    'phone' => $order->farmer->phone,
                    'address' => $order->pickup_address,
                    'landmark' => $order->landmark,
                ] : null,
                'products' => collect($order->items ?? [])->map(function ($item) {
                    return [
                        'name' => $item['product'] ?? $item['name'] ?? 'Unknown',
                        'category' => $item['category'] ?? 'General',
                        'quantity' => $item['quantity'] ?? '0',
                    ];
                })->values()->all(),
                'pickup_location' => [
                    'address' => $order->pickup_address,
                    'landmark' => $order->landmark,
                ],
                'drop_location' => [
                    'address' => $order->drop_address,
                    'contact_person' => $order->drop_contact_person,
                    'contact_phone' => $order->drop_contact_phone,
                ],
                'order_timeline' => $timeline,
                'delivery_summary' => $deliverySummary,
                'current_status' => [
                    'status' => strtoupper($order->status),
                    'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
                    'timestamp' => $order->delivered_at ? $order->delivered_at->format('M d, Y • h:i A') : null,
                ],
            ],
        ]);
    }

    /**
     * Format duration in minutes to human readable format (e.g., "1h 30m")
     */
    private function formatDuration($minutes)
    {
        if ($minutes < 60) {
            return $minutes . 'm';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($mins > 0) {
            return $hours . 'h ' . $mins . 'm';
        }
        
        return $hours . 'h';
    }

    /**
     * Get delivery boy profile
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $profile = DeliveryBoyProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'role_title' => 'Lead Delivery Partner',
                'shift_start_time' => '06:00',
                'shift_end_time' => '14:00',
                'vehicle_type' => 'EV two-wheeler',
                'preferred_zone' => 'Central Bengaluru',
                'auto_accept_urgent_jobs' => false,
                'share_live_location' => false,
            ]
        );

        // Format shift window
        $shiftWindow = null;
        if ($profile->shift_start_time && $profile->shift_end_time) {
            $shiftWindow = $this->formatTime($profile->shift_start_time) . ' - ' . $this->formatTime($profile->shift_end_time);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                ],
                'profile' => [
                    'vehicle_id' => $profile->vehicle_id,
                    'role_title' => $profile->role_title ?? 'Lead Delivery Partner',
                    'is_verified' => $profile->is_verified ?? false,
                ],
                'shifts_preferences' => [
                    'shift_window' => $shiftWindow,
                    'shift_start_time' => $profile->shift_start_time,
                    'shift_end_time' => $profile->shift_end_time,
                    'vehicle_type' => $profile->vehicle_type,
                    'preferred_zone' => $profile->preferred_zone,
                ],
                'settings' => [
                    'auto_accept_urgent_jobs' => $profile->auto_accept_urgent_jobs ?? false,
                    'share_live_location' => $profile->share_live_location ?? false,
                ],
            ],
        ]);
    }

    /**
     * Update delivery boy profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $data = $request->validate([
            'vehicle_id' => ['nullable', 'string', 'max:255'],
            'role_title' => ['nullable', 'string', 'max:255'],
            'shift_start_time' => ['nullable', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'shift_end_time' => ['nullable', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'vehicle_type' => ['nullable', 'string', 'max:255'],
            'preferred_zone' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = DeliveryBoyProfile::firstOrCreate(['user_id' => $user->id]);
        $profile->update($data);

        // Format shift window for response
        $shiftWindow = null;
        if ($profile->shift_start_time && $profile->shift_end_time) {
            $shiftWindow = $this->formatTime($profile->shift_start_time) . ' - ' . $this->formatTime($profile->shift_end_time);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'shifts_preferences' => [
                    'shift_window' => $shiftWindow,
                    'shift_start_time' => $profile->shift_start_time,
                    'shift_end_time' => $profile->shift_end_time,
                    'vehicle_type' => $profile->vehicle_type,
                    'preferred_zone' => $profile->preferred_zone,
                ],
            ],
        ]);
    }

    /**
     * Update delivery boy settings
     */
    public function updateSettings(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'delivery_boy') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $data = $request->validate([
            'auto_accept_urgent_jobs' => ['nullable', 'boolean'],
            'share_live_location' => ['nullable', 'boolean'],
        ]);

        $profile = DeliveryBoyProfile::firstOrCreate(['user_id' => $user->id]);
        $profile->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
            'data' => [
                'settings' => [
                    'auto_accept_urgent_jobs' => $profile->auto_accept_urgent_jobs,
                    'share_live_location' => $profile->share_live_location,
                ],
            ],
        ]);
    }

    /**
     * Format time from 24-hour format to 12-hour format with AM/PM
     */
    private function formatTime($time)
    {
        if (!$time) {
            return null;
        }

        $parts = explode(':', $time);
        $hour = (int) $parts[0];
        $minute = $parts[1] ?? '00';

        $period = 'AM';
        if ($hour >= 12) {
            $period = 'PM';
            if ($hour > 12) {
                $hour -= 12;
            }
        }
        if ($hour == 0) {
            $hour = 12;
        }

        return sprintf('%d:%s %s', $hour, $minute, $period);
    }
}

