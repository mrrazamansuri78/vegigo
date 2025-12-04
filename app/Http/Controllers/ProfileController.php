<?php

namespace App\Http\Controllers;

use App\Models\FarmerProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $profile = FarmerProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'farm_name' => $request->user()->name,
                'location_city' => null,
                'location_state' => null,
                'farm_size_acres' => null,
                'primary_crop' => null,
                'storage' => null,
                'certifications' => null,
                'fulfillment_rate' => 98,
                'average_rating' => 4.9,
                'repeat_partners' => 12,
            ],
        );

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
                'profile' => $profile,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'farm_name' => ['nullable', 'string', 'max:255'],
            'location_city' => ['nullable', 'string', 'max:255'],
            'location_state' => ['nullable', 'string', 'max:255'],
            'farm_size_acres' => ['nullable', 'numeric', 'min:0'],
            'primary_crop' => ['nullable', 'string', 'max:255'],
            'storage' => ['nullable', 'string', 'max:255'],
            'certifications' => ['nullable', 'string', 'max:255'],
            'fulfillment_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'average_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'repeat_partners' => ['nullable', 'integer', 'min:0'],
        ]);

        $profile = FarmerProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
        );

        $profile->update($data);

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }
}


