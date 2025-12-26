<?php

namespace App\Http\Controllers;

use App\Models\FarmerProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $data = ['user' => $user];

        if ($user->role === 'farmer') {
            $data['profile'] = FarmerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'farm_name' => $user->name,
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
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Validate common user fields
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['sometimes', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            
            // Farmer Profile fields
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
            'khasra_no' => ['nullable', 'string', 'max:255'],
            
            // Legacy/Direct User Location fields
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        // Update User Model Fields
        $userFillable = ['name', 'email', 'phone', 'address', 'latitude', 'longitude', 'profile_image'];
        $userData = array_intersect_key($data, array_flip($userFillable));
        
        if ($request->hasFile('profile_image')) {
            $userData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        if (!empty($userData)) {
            $user->update($userData);
        }

        // Update Farmer Profile if applicable
        if ($user->role === 'farmer') {
            $profile = FarmerProfile::firstOrCreate(['user_id' => $user->id]);
            $profileData = array_diff_key($data, array_flip($userFillable));
            if (!empty($profileData)) {
                $profile->update($profileData);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->fresh(),
                'profile' => $user->role === 'farmer' ? FarmerProfile::where('user_id', $user->id)->first() : null,
            ],
        ]);
    }
}


