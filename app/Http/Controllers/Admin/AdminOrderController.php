<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\FirebaseService;

class AdminOrderController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $query = Order::with(['farmer:id,name,phone', 'deliveryBoy:id,name,phone']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['farmer', 'deliveryBoy']);

        // Get Firebase location if available
        try {
            $firebaseLocation = $this->firebase->getOrderLocation($order->id);
        } catch (\Exception $e) {
            $firebaseLocation = null;
        }

        return view('admin.orders.show', compact('order', 'firebaseLocation'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ready,accepted,picked_up,delivered',
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        // Update Firebase
        try {
            $this->firebase->updateOrderStatus($order->id, $validated['status']);
        } catch (\Exception $e) {
            // Log error but don't fail
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}

