<?php

namespace App\Http\Controllers;

use App\Models\OtpCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'role' => ['required', Rule::in(['farmer', 'delivery_boy'])],
            'purpose' => ['nullable', 'string'],
        ]);

        $code = random_int(1000, 9999);

        OtpCode::create([
            'phone' => $data['phone'],
            'role' => $data['role'],
            'code' => (string) $code,
            'purpose' => $data['purpose'] ?? 'login',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // TODO: Integrate actual SMS provider here.

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
            'debug_code' => $code, // remove in production
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'role' => ['required', Rule::in(['farmer', 'delivery_boy'])],
            'code' => ['required', 'string'],
        ]);

        $otp = OtpCode::where('phone', $data['phone'])
            ->where('role', $data['role'])
            ->where('code', $data['code'])
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (! $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        $otp->used = true;
        $otp->save();

        $placeholderEmail = sprintf('user%s@farmlink.local', preg_replace('/\D+/', '', $data['phone']));

        $user = User::firstOrCreate(
            ['phone' => $data['phone']],
            [
                'name' => 'Farmer '.$data['phone'],
                'email' => $placeholderEmail,
                'role' => $data['role'],
                'password' => bcrypt(Str::random(16)),
            ],
        );

        if ($user->role !== $data['role']) {
            $user->role = $data['role'];
            $user->save();
        }

        $token = Str::random(60);
        $user->api_token = hash('sha256', $token);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'OTP verified.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function signup(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['farmer', 'delivery_boy'])],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'role' => $data['role'],
            'password' => bcrypt($data['password']),
        ]);

        $token = Str::random(60);
        $user->api_token = hash('sha256', $token);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Account created.',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('phone', $data['phone'])->first();

        if (! $user || ! password_verify($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = Str::random(60);
        $user->api_token = hash('sha256', $token);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Clear API token
        $user->api_token = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}


