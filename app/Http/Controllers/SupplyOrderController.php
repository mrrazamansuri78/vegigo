<?php

namespace App\Http\Controllers;

use App\Models\SupplyOrder;
use App\Models\Product;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplyOrderController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {
        // Farmers see their own orders
        $orders = SupplyOrder::where('farmer_id', $request->user()->id)
            ->with('product')
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
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);

            // Ensure the product belongs to the farmer (optional validation, depending on business logic)
            // if ($product->user_id !== $request->user()->id) {
            //     return response()->json(['success' => false, 'message' => 'Unauthorized product'], 403);
            // }

            $supplyOrder = SupplyOrder::create([
                'farmer_id' => $request->user()->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'unit' => $request->unit,
                'status' => 'pending',
            ]);

            DB::commit();

            // Notify Firebase (Admin Notification)
            try {
                $this->firebase->updateSupplyOrder($supplyOrder->id, 'pending', [
                    'farmer_name' => $request->user()->name,
                    'product_name' => $product->name,
                    'quantity' => $supplyOrder->quantity,
                    'created_at' => $supplyOrder->created_at->toIso8601String(),
                ]);
            } catch (\Exception $e) {
                // Log error
            }

            return response()->json([
                'success' => true,
                'message' => 'Supply order submitted successfully.',
                'data' => $supplyOrder->load('product'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
