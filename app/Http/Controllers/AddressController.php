<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', $request->user()->id)->get();
        return response()->json(['success' => true, 'data' => $addresses]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
        ]);

        if (isset($data['is_default']) && $data['is_default']) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        $address = Address::create([
            'user_id' => $request->user()->id,
            ...$data
        ]);

        return response()->json(['success' => true, 'data' => $address], 201);
    }

    public function update(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);
        
        $data = $request->validate([
            'label' => 'sometimes|string',
            'address' => 'sometimes|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
        ]);

        if (isset($data['is_default']) && $data['is_default']) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return response()->json(['success' => true, 'data' => $address]);
    }

    public function destroy(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);
        $address->delete();

        return response()->json(['success' => true, 'message' => 'Address deleted']);
    }
}
