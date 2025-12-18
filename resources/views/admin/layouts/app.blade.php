<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Vegigo</title>
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
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBdP-I7KzDCZJwEnUBEpzLBkRXAstS2Yis&libraries=places"></script>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-vegigo-dark-teal to-vegigo-teal text-white flex flex-col shadow-xl">
            <div class="p-6 border-b border-teal-700">
                <div class="flex items-center justify-center mb-3">
                    <img src="{{ asset('vegigo-logo.jpg') }}" alt="Vegigo Logo" class="h-12 w-auto object-contain">
                </div>
                <h1 class="text-xl font-bold text-center">
                    <span class="text-vegigo-green">VEGI</span><span class="text-vegigo-orange">GO</span>
                    <span class="block text-xs text-teal-200 mt-1 font-normal">Admin Panel</span>
                </h1>
            </div>
            
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-teal-700 transition {{ request()->routeIs('admin.dashboard') ? 'bg-teal-700 shadow-lg' : '' }}">
                    <i class="fas fa-chart-line w-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-teal-700 transition {{ request()->routeIs('admin.orders.*') ? 'bg-teal-700 shadow-lg' : '' }}">
                    <i class="fas fa-shopping-cart w-5 mr-3"></i>
                    Orders
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-teal-700 transition {{ request()->routeIs('admin.products.*') ? 'bg-teal-700 shadow-lg' : '' }}">
                    <i class="fas fa-box w-5 mr-3"></i>
                    Products
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-teal-700 transition {{ request()->routeIs('admin.inventory.*') ? 'bg-teal-700 shadow-lg' : '' }}">
                    <i class="fas fa-warehouse w-5 mr-3"></i>
                    Inventory
                </a>
                <a href="{{ route('admin.supply.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-teal-700 transition {{ request()->routeIs('admin.supply.*') ? 'bg-teal-700 shadow-lg' : '' }}">
                    <i class="fas fa-tractor w-5 mr-3"></i>
                    Farmer Supply
                </a>
            </nav>
            
            <div class="p-4 border-t border-teal-700">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 rounded-lg hover:bg-red-600 transition bg-red-500/20 hover:bg-red-600">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-md border-b-2 border-vegigo-green/20">
                <div class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-white to-teal-50/30">
                    <h2 class="text-xl font-bold text-gray-800">
                        <span class="text-vegigo-green">@yield('page-title', 'Dashboard')</span>
                    </h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-gray-700 bg-teal-50 px-4 py-2 rounded-full">
                            <i class="fas fa-user-circle mr-2 text-vegigo-teal"></i>
                            {{ auth()->user()->name }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="mb-4 bg-vegigo-green/10 border-l-4 border-vegigo-green text-vegigo-teal px-4 py-3 rounded-lg shadow-sm relative" role="alert">
                        <span class="block sm:inline font-medium"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg shadow-sm relative" role="alert">
                        <span class="block sm:inline font-medium"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

