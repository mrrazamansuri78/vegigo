<?php

namespace App\Http\Controllers;

use App\Models\SupplyOrder;
use App\Models\VendorAllocation;
use App\Models\Product;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminInventoryController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function summary()
    {
        $products = Product::select('id', 'name', 'quantity', 'unit', 'status')
            ->where('status', 'active')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function supplyOrders(Request $request)
    {
        $query = SupplyOrder::with(['farmer:id,name,phone', 'product:id,name,unit']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function updateSupplyStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
            'admin_note' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $order = SupplyOrder::findOrFail($id);

            if ($order->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Order already processed'], 400);
            }

            $order->update([
                'status' => $request->status,
                'admin_note' => $request->admin_note,
            ]);

            if ($request->status === 'accepted') {
                // Increase product stock
                $product = Product::find($order->product_id);
                if ($product) {
                    $product->increment('quantity', $order->quantity);
                    try {
                        $this->firebase->updateProductStock($product->id, $product->quantity);
                    } catch (\Exception $e) {
                        // Log error
                    }
                }
            }

            // Update supply order status in Firebase
            try {
                $this->firebase->updateSupplyOrder($order->id, $request->status, [
                    'admin_note' => $request->admin_note,
                ]);
            } catch (\Exception $e) {
                // Log error
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supply order updated successfully',
                'data' => $order,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function allocateToVendor(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.1',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::lockForUpdate()->find($request->product_id);

            if ($product->quantity < $request->quantity) {
                throw new \Exception("Insufficient stock. Available: {$product->quantity}");
            }

            $product->decrement('quantity', $request->quantity);

            // Sync to Firebase
            try {
                $this->firebase->updateProductStock($product->id, $product->quantity);
            } catch (\Exception $e) {
                // Log error
            }

            $allocation = VendorAllocation::create([
                'vendor_id' => $request->vendor_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'status' => 'allocated',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock allocated to vendor successfully',
                'data' => $allocation,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function vendorAllocations(Request $request)
    {
        $allocations = VendorAllocation::with(['vendor:id,name,phone', 'product:id,name,unit'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $allocations,
        ]);
    }
}
