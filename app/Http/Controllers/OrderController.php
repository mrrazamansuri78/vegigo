<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('orderItems.product')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'drop_address' => 'required|string',
            'drop_latitude' => 'nullable|numeric',
            'drop_longitude' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $product->decrement('quantity', $item['quantity']);
                
                // Sync stock to Firebase
                try {
                    $this->firebase->updateProductStock($product->id, $product->quantity);
                } catch (\Exception $e) {
                    // Log error
                }

                $price = $product->price_per_unit * $item['quantity'];
                $totalAmount += $price;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price_per_unit,
                ];
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => 'ORD-' . strtoupper(uniqid()),
                'customer_name' => $request->user()->name ?? 'Customer',
                'drop_address' => $request->drop_address,
                'drop_latitude' => $request->drop_latitude,
                'drop_longitude' => $request->drop_longitude,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'pickup_address' => 'Central Warehouse',
            ]);

            foreach ($orderItemsData as $data) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'price' => $data['unit_price'],
                ]);
            }

            DB::commit();

            // Notify Firebase
            try {
                $this->firebase->updateOrderStatus($order->id, 'pending', [
                    'customer_name' => $order->customer_name,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->toIso8601String(),
                ]);
            } catch (\Exception $e) {
                // Log error but continue
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'data' => $order->load('orderItems.product'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function track($id, Request $request)
    {
        $order = Order::with(['deliveryBoy.deliveryBoyProfile', 'orderItems.product'])->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Check permission (user owns order or is admin)
        if ($request->user()->role !== 'admin' && $order->user_id !== $request->user()->id) {
             return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $trackingData = [
            'order_id' => $order->id,
            'status' => $order->status,
            'estimated_delivery' => $order->created_at->addMinutes(45)->format('Y-m-d H:i:s'),
            'delivery_boy' => null,
            'drop_location' => [
                'latitude' => $order->drop_latitude,
                'longitude' => $order->drop_longitude,
                'address' => $order->drop_address,
            ]
        ];

        if ($order->deliveryBoy && $order->deliveryBoy->deliveryBoyProfile) {
            $profile = $order->deliveryBoy->deliveryBoyProfile;
            $trackingData['delivery_boy'] = [
                'name' => $order->deliveryBoy->name,
                'phone' => $order->deliveryBoy->phone,
                'current_latitude' => $profile->current_latitude,
                'current_longitude' => $profile->current_longitude,
                'last_updated' => $profile->updated_at,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $trackingData,
        ]);
    }
}
