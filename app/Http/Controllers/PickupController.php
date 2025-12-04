<?php

namespace App\Http\Controllers;

use App\Models\PickupRequest;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_summary' => ['nullable', 'string'],
            'preferred_date' => ['nullable', 'date'],
        ]);

        $pickup = PickupRequest::create([
            'user_id' => $request->user()->id,
            'product_summary' => $data['product_summary'] ?? null,
            'preferred_date' => $data['preferred_date'] ?? null,
            'status' => 'requested',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pickup requested successfully.',
            'data' => $pickup,
        ], 201);
    }
}


