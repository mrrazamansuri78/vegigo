<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Vegigo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'vegigo-green': '#22c55e',
                        'vegigo-orange': '#f97316',
                        'vegigo-teal': '#0f766e',
                        'vegigo-dark-teal': '#134e4a',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-vegigo-dark-teal via-vegigo-teal to-teal-600 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md border-t-4 border-vegigo-green">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('vegigo-logo.jpg') }}" alt="Vegigo Logo" class="h-20 w-auto object-contain">
            </div>
            <h1 class="text-3xl font-bold">
                <span class="text-vegigo-green">VEGI</span><span class="text-vegigo-orange">GO</span>
                <span class="block text-lg text-gray-600 mt-1 font-normal">Admin Panel</span>
            </h1>
            <p class="text-gray-600 mt-2 text-sm">Farm to Fork, Fast</p>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    <i class="fas fa-envelope mr-2"></i>Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vegigo-green focus:border-vegigo-green outline-none transition"
                    placeholder="admin@vegigo.com"
                    required
                >
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vegigo-green focus:border-vegigo-green outline-none transition"
                    placeholder="••••••••"
                    required
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-vegigo-green to-emerald-600 text-white font-bold py-3 px-4 rounded-lg hover:from-vegigo-green hover:to-emerald-700 focus:outline-none focus:shadow-lg focus:ring-2 focus:ring-vegigo-green transform transition hover:scale-105 shadow-md"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>
        </form>
    </div>
</body>
</html>

