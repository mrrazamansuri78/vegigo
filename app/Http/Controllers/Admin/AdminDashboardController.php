<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Services\FirebaseService;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function dashboard()
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'active_orders' => Order::whereIn('status', ['accepted', 'picked_up'])->count(),
            'delivered_today' => Order::where('status', 'delivered')
                ->whereDate('delivered_at', today())
                ->count(),
            'total_farmers' => User::where('role', 'farmer')->count(),
            'total_delivery_boys' => User::where('role', 'delivery_boy')->count(),
            'total_products' => Product::count(),
        ];

        $recent_orders = Order::with(['farmer:id,name,phone', 'deliveryBoy:id,name,phone'])
            ->latest()
            ->limit(10)
            ->get();

        $today_earnings = Order::where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->sum('total_amount') ?? 0;

        return view('admin.dashboard', compact('stats', 'recent_orders', 'today_earnings'));
    }

    public function getLiveOrders()
    {
        try {
            $activeOrders = Order::with(['farmer:id,name,phone', 'deliveryBoy:id,name,phone'])
                ->whereIn('status', ['accepted', 'picked_up'])
                ->get()
                ->map(function ($order) {
                    // Try to get Firebase location
                    try {
                        $firebaseLocation = $this->firebase->getOrderLocation($order->id);
                    } catch (\Exception $e) {
                        $firebaseLocation = null;
                    }

                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'status' => $order->status,
                        'farmer' => $order->farmer ? [
                            'name' => $order->farmer->name,
                            'phone' => $order->farmer->phone,
                        ] : null,
                        'delivery_boy' => $order->deliveryBoy ? [
                            'name' => $order->deliveryBoy->name,
                            'phone' => $order->deliveryBoy->phone,
                        ] : null,
                        'pickup_address' => $order->pickup_address,
                        'drop_address' => $order->drop_address,
                        'pickup_lat' => $order->pickup_latitude ?? null,
                        'pickup_lng' => $order->pickup_longitude ?? null,
                        'drop_lat' => $order->drop_latitude ?? null,
                        'drop_lng' => $order->drop_longitude ?? null,
                        'current_lat' => $firebaseLocation['latitude'] ?? null,
                        'current_lng' => $firebaseLocation['longitude'] ?? null,
                        'distance_km' => $order->distance_km,
                        'created_at' => $order->created_at->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'orders' => $activeOrders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

