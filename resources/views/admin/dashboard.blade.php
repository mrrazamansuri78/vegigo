@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-vegigo-teal hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Orders</p>
                    <p class="text-3xl font-bold text-vegigo-teal mt-2">{{ $stats['total_orders'] }}</p>
                </div>
                <div class="bg-vegigo-green/10 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-vegigo-green text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-vegigo-orange hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Pending Orders</p>
                    <p class="text-3xl font-bold text-vegigo-orange mt-2">{{ $stats['pending_orders'] }}</p>
                </div>
                <div class="bg-vegigo-orange/10 p-3 rounded-full">
                    <i class="fas fa-clock text-vegigo-orange text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-vegigo-green hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Active Deliveries</p>
                    <p class="text-3xl font-bold text-vegigo-green mt-2">{{ $stats['active_orders'] }}</p>
                </div>
                <div class="bg-vegigo-green/10 p-3 rounded-full">
                    <i class="fas fa-truck text-vegigo-green text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-vegigo-teal hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Today's Earnings</p>
                    <p class="text-3xl font-bold text-vegigo-teal mt-2">₹{{ number_format($today_earnings, 2) }}</p>
                </div>
                <div class="bg-vegigo-teal/10 p-3 rounded-full">
                    <i class="fas fa-rupee-sign text-vegigo-teal text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Order Tracking -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-vegigo-green/20">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-map-marker-alt text-vegigo-green mr-2"></i>
                Live Order Tracking
            </h3>
            <button onclick="refreshLiveOrders()" class="px-4 py-2 bg-gradient-to-r from-vegigo-green to-emerald-600 text-white rounded-lg hover:from-vegigo-green hover:to-emerald-700 transition shadow-md font-medium">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
        </div>
        <div id="map" class="w-full h-96 rounded-lg border-2 border-vegigo-green/30 shadow-inner"></div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-vegigo-green/20">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            <span class="text-vegigo-teal">Recent Orders</span>
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Farmer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recent_orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-900">{{ $order->order_code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->customer_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($order->status == 'pending') bg-vegigo-orange/20 text-vegigo-orange border border-vegigo-orange/30
                                @elseif($order->status == 'delivered') bg-vegigo-green/20 text-vegigo-green border border-vegigo-green/30
                                @else bg-vegigo-teal/20 text-vegigo-teal border border-vegigo-teal/30
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->farmer->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-vegigo-green hover:text-vegigo-teal font-semibold">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let map;
let markers = [];
let infoWindows = [];

function initMap() {
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.error('Google Maps API not loaded. Please check your API key.');
        document.getElementById('map').innerHTML = '<div class="flex items-center justify-center h-full text-red-600 bg-red-50 rounded-lg"><div class="text-center"><i class="fas fa-exclamation-triangle text-4xl mb-2"></i><p class="font-semibold">Google Maps failed to load</p><p class="text-sm mt-1">Please check API key configuration in Google Cloud Console</p></div></div>';
        return;
    }

    try {
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 12.9716, lng: 77.5946 },
            zoom: 12,
            styles: [
                {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }
            ]
        });
        
        loadLiveOrders();
        setInterval(loadLiveOrders, 10000);
    } catch (error) {
        console.error('Error initializing map:', error);
        document.getElementById('map').innerHTML = '<div class="flex items-center justify-center h-full text-red-600 bg-red-50 rounded-lg"><div class="text-center"><i class="fas fa-exclamation-triangle text-4xl mb-2"></i><p class="font-semibold">Map initialization error</p><p class="text-sm mt-1">' + error.message + '</p></div></div>';
    }
}

function loadLiveOrders() {
    fetch('{{ route("admin.api.live-orders") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateMapMarkers(data.orders);
            }
        })
        .catch(error => console.error('Error:', error));
}

function updateMapMarkers(orders) {
    markers.forEach(marker => marker.setMap(null));
    infoWindows.forEach(window => window.close());
    markers = [];
    infoWindows = [];

    if (orders.length === 0) return;

    const bounds = new google.maps.LatLngBounds();

    orders.forEach(order => {
        if (order.pickup_lat && order.pickup_lng) {
            const pickupMarker = new google.maps.Marker({
                position: { lat: parseFloat(order.pickup_lat), lng: parseFloat(order.pickup_lng) },
                map: map,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                },
                title: 'Pickup: ' + order.pickup_address
            });

            const pickupInfo = new google.maps.InfoWindow({
                content: `
                    <div class="p-2">
                        <h3 class="font-bold text-green-600">Pickup Location</h3>
                        <p>${order.pickup_address}</p>
                        <p class="text-sm text-gray-600">Order: ${order.order_code}</p>
                    </div>
                `
            });

            pickupMarker.addListener('click', () => {
                infoWindows.forEach(w => w.close());
                pickupInfo.open(map, pickupMarker);
            });

            markers.push(pickupMarker);
            infoWindows.push(pickupInfo);
            bounds.extend(pickupMarker.getPosition());
        }

        if (order.drop_lat && order.drop_lng) {
            const dropMarker = new google.maps.Marker({
                position: { lat: parseFloat(order.drop_lat), lng: parseFloat(order.drop_lng) },
                map: map,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                },
                title: 'Drop: ' + order.drop_address
            });

            const dropInfo = new google.maps.InfoWindow({
                content: `
                    <div class="p-2">
                        <h3 class="font-bold text-red-600">Drop Location</h3>
                        <p>${order.drop_address}</p>
                        <p class="text-sm text-gray-600">Order: ${order.order_code}</p>
                    </div>
                `
            });

            dropMarker.addListener('click', () => {
                infoWindows.forEach(w => w.close());
                dropInfo.open(map, dropMarker);
            });

            markers.push(dropMarker);
            infoWindows.push(dropInfo);
            bounds.extend(dropMarker.getPosition());
        }

        if (order.current_lat && order.current_lng) {
            const currentMarker = new google.maps.Marker({
                position: { lat: parseFloat(order.current_lat), lng: parseFloat(order.current_lng) },
                map: map,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                },
                title: 'Current Location - ' + order.order_code
            });

            const currentInfo = new google.maps.InfoWindow({
                content: `
                    <div class="p-2">
                        <h3 class="font-bold text-blue-600">Current Location</h3>
                        <p>Order: ${order.order_code}</p>
                        <p class="text-sm">Status: ${order.status}</p>
                        ${order.delivery_boy ? `<p class="text-sm">Delivery Boy: ${order.delivery_boy.name}</p>` : ''}
                    </div>
                `
            });

            currentMarker.addListener('click', () => {
                infoWindows.forEach(w => w.close());
                currentInfo.open(map, currentMarker);
            });

            markers.push(currentMarker);
            infoWindows.push(currentInfo);
            bounds.extend(currentMarker.getPosition());
        }
    });

    if (bounds.isEmpty() === false) {
        map.fitBounds(bounds);
    }
}

function refreshLiveOrders() {
    loadLiveOrders();
}

window.addEventListener('load', initMap);
</script>
@endpush

