@extends('admin.layouts.app')

@section('title', 'Order Details')
@section('page-title', 'Order Details')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Order #{{ $order->order_code }}</h2>
                <p class="text-gray-600">Created: {{ $order->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <span class="px-4 py-2 text-sm font-semibold rounded-full 
                @if($order->status == 'pending') bg-vegigo-orange/20 text-vegigo-orange border-2 border-vegigo-orange/30
                @elseif($order->status == 'delivered') bg-vegigo-green/20 text-vegigo-green border-2 border-vegigo-green/30
                @else bg-vegigo-teal/20 text-vegigo-teal border-2 border-vegigo-teal/30
                @endif">
                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Customer Information</h3>
                <p class="text-gray-600">{{ $order->customer_name }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Farmer</h3>
                <p class="text-gray-600">{{ $order->farmer->name ?? 'N/A' }}</p>
                <p class="text-sm text-gray-500">{{ $order->farmer->phone ?? '' }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Delivery Boy</h3>
                <p class="text-gray-600">{{ $order->deliveryBoy->name ?? 'Not Assigned' }}</p>
                @if($order->deliveryBoy)
                    <p class="text-sm text-gray-500">{{ $order->deliveryBoy->phone }}</p>
                @endif
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Distance</h3>
                <p class="text-gray-600">{{ $order->distance_km ?? 'N/A' }} km</p>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-2">Order Items</h3>
            <div class="space-y-2">
                @foreach($order->items ?? [] as $item)
                <div class="flex justify-between p-3 bg-gray-50 rounded">
                    <span class="text-gray-800">{{ $item['product'] ?? $item['name'] ?? 'Unknown' }}</span>
                    <span class="text-gray-600">{{ $item['quantity'] ?? '0' }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-4">Update Status</h3>
            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="flex items-center space-x-4">
                @csrf
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>Ready</option>
                    <option value="accepted" {{ $order->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="picked_up" {{ $order->status == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                    <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                </select>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-vegigo-green to-emerald-600 text-white rounded-lg hover:from-vegigo-green hover:to-emerald-700 shadow-md font-medium">
                    Update Status
                </button>
            </form>
        </div>

        <div>
            <h3 class="font-semibold text-gray-700 mb-4">Order Tracking Map</h3>
            <div id="orderMap" class="w-full h-96 rounded-lg border border-gray-200"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function initOrderMap() {
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.error('Google Maps API not loaded. Please check your API key.');
        document.getElementById('orderMap').innerHTML = '<div class="flex items-center justify-center h-full text-red-600"><p>Google Maps failed to load. Please check API key configuration.</p></div>';
        return;
    }

    const map = new google.maps.Map(document.getElementById('orderMap'), {
        center: { lat: 12.9716, lng: 77.5946 },
        zoom: 12
    });

    const directionsService = new google.maps.DirectionsService();
    const directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);

    @if($order->pickup_latitude && $order->pickup_longitude)
    const pickup = { lat: {{ $order->pickup_latitude }}, lng: {{ $order->pickup_longitude }} };
    new google.maps.Marker({
        position: pickup,
        map: map,
        icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
        title: 'Pickup: {{ $order->pickup_address }}'
    });
    @endif

    @if($order->drop_latitude && $order->drop_longitude)
    const drop = { lat: {{ $order->drop_latitude }}, lng: {{ $order->drop_longitude }} };
    new google.maps.Marker({
        position: drop,
        map: map,
        icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
        title: 'Drop: {{ $order->drop_address }}'
    });
    @endif

    @if($firebaseLocation && isset($firebaseLocation['latitude']) && isset($firebaseLocation['longitude']))
    const current = { lat: {{ $firebaseLocation['latitude'] }}, lng: {{ $firebaseLocation['longitude'] }} };
    new google.maps.Marker({
        position: current,
        map: map,
        icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
        title: 'Current Location'
    });
    @endif

    @if($order->pickup_latitude && $order->pickup_longitude && $order->drop_latitude && $order->drop_longitude)
    directionsService.route({
        origin: pickup,
        destination: drop,
        travelMode: 'DRIVING'
    }, (response, status) => {
        if (status === 'OK') {
            directionsRenderer.setDirections(response);
        }
    });
    @endif
}

window.addEventListener('load', initOrderMap);
</script>
@endpush

