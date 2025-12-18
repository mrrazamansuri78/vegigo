<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getCart($user)
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    public function index(Request $request)
    {
        $cart = $this->getCart($request->user());
        $items = $cart->items()->with('product')->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.1',
        ]);

        $cart = $this->getCart($request->user());
        
        $cartItem = CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return response()->json(['success' => true, 'message' => 'Item added to cart', 'data' => $cartItem]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.1',
        ]);

        $cart = $this->getCart($request->user());
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($id);
        
        $item->update(['quantity' => $request->quantity]);

        return response()->json(['success' => true, 'message' => 'Cart updated', 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $cart = $this->getCart($request->user());
        CartItem::where('cart_id', $cart->id)->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Item removed from cart']);
    }

    public function clear(Request $request)
    {
        $cart = $this->getCart($request->user());
        $cart->items()->delete();

        return response()->json(['success' => true, 'message' => 'Cart cleared']);
    }
}
