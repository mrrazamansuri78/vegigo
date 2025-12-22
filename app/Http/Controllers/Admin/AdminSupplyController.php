<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PickupRequest;
use App\Models\SupplyOrder;
use App\Models\User;

class AdminSupplyController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $requests = PickupRequest::with(['farmer:id,name,phone'])
            ->latest()
            ->paginate(20, ['*'], 'requests_page');

        $supplyOrders = SupplyOrder::with(['farmer:id,name,phone', 'product:id,name,unit'])
            ->latest()
            ->paginate(20, ['*'], 'orders_page');

        return view('admin.supply.index', compact('requests', 'supplyOrders'));
    }

    public function updateStatus(Request $request, PickupRequest $pickupRequest)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,accepted,rejected,scheduled',
        ]);

        $pickupRequest->update(['status' => $validated['status']]);

        return redirect()->route('admin.supply.index')
            ->with('success', 'Supply request status updated.');
    }
}

