<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimpleTokenGuardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::viaRequest('simple_token', function (Request $request): ?User {
            $header = $request->header('Authorization');

            if (! $header || ! str_starts_with($header, 'Bearer ')) {
                return null;
            }

            $plainToken = substr($header, 7);
            $hashed = hash('sha256', $plainToken);

            return User::where('api_token', $hashed)->first();
        });
    }
}


