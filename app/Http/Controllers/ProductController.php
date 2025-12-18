<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {
        $query = Product::query();

        // If user is farmer, show only their products.
        // If user is customer/admin/delivery_boy, show all available products.
        if ($request->user()->role === 'farmer') {
             $query->where('user_id', $request->user()->id);
        } else {
             $query->where('status', 'active')->where('quantity', '>', 0);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:10'],
            'is_organic' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'quantity' => $data['quantity'],
            'price_per_unit' => $data['price_per_unit'],
            'unit' => $data['unit'] ?? 'kg',
            'is_organic' => $data['is_organic'] ?? false,
            'image_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to marketplace.',
            'data' => $product,
        ], 201);
    }

    public function suggested()
    {
        // Static suggestions for now – later can be dynamic.
        $suggestions = [
            [
                'name' => 'Cherry Tomato',
                'category' => 'vegetables',
                'price_per_unit' => 45.0,
                'unit' => 'kg',
                'available_quantity' => 120.0,
                'tags' => ['Organic'],
            ],
            [
                'name' => 'Baby Spinach',
                'category' => 'vegetables',
                'price_per_unit' => 35.0,
                'unit' => 'kg',
                'available_quantity' => 80.0,
                'tags' => [],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }
}


