<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->latest('ready_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }
}


