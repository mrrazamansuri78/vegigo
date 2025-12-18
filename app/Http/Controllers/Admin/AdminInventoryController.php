<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\SupplyOrder;
use App\Models\VendorAllocation;
use App\Models\User;
use App\Services\FirebaseService;

class AdminInventoryController extends Controller
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

        $categoryTotals = Product::selectRaw('category, SUM(quantity) as total_quantity')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $totalStock = Product::sum('quantity');

        $products = Product::with('farmer:id,name,phone')
            ->orderBy('name')
            ->paginate(20);

        $supplyOrders = SupplyOrder::with(['farmer', 'product'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $vendors = User::where('role', 'vendor')->get();

        return view('admin.inventory.index', compact('categoryTotals', 'totalStock', 'products', 'supplyOrders', 'vendors'));
    }

    public function updateSupplyStatus(Request $request, $id)
    {
        $supplyOrder = SupplyOrder::findOrFail($id);
        $status = $request->input('status'); // approved, rejected

        if ($status === 'approved' && $supplyOrder->status !== 'approved') {
            // Increment product stock
            $product = Product::findOrFail($supplyOrder->product_id);
            $product->increment('quantity', $supplyOrder->quantity);
            
            try {
                $this->firebase->updateProductStock($product->id, $product->quantity);
            } catch (\Exception $e) {
                // Log error
            }
        }

        $supplyOrder->update([
            'status' => $status,
            'admin_note' => $request->input('admin_note'),
        ]);

        try {
            $this->firebase->updateSupplyOrder($supplyOrder->id, $status, [
                'admin_note' => $request->input('admin_note'),
            ]);
        } catch (\Exception $e) {
            // Log error
        }

        return redirect()->back()->with('success', 'Supply order updated successfully.');
    }

    public function allocateToVendor(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->quantity < $request->quantity) {
            return redirect()->back()->with('error', 'Insufficient stock.');
        }

        // Decrement stock
        $product->decrement('quantity', $request->quantity);

        try {
            $this->firebase->updateProductStock($product->id, $product->quantity);
        } catch (\Exception $e) {
            // Log error
        }

        // Create allocation record
        VendorAllocation::create([
            'vendor_id' => $request->vendor_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'status' => 'allocated',
        ]);

        return redirect()->back()->with('success', 'Stock allocated to vendor successfully.');
    }
}
