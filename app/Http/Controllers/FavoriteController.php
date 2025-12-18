<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with('product')
            ->get();

        return response()->json(['success' => true, 'data' => $favorites]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Added to favorites']);
    }

    public function destroy(Request $request, $productId)
    {
        Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Removed from favorites']);
    }
}
