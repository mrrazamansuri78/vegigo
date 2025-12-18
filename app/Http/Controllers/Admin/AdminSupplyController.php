<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PickupRequest;
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
            ->paginate(20);

        return view('admin.supply.index', compact('requests'));
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

