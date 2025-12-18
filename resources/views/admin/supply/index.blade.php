@extends('admin.layouts.app')

@section('title', 'Farmer Supply')
@section('page-title', 'Farmer Supply Requests')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Pickup Requests</h3>
            <span class="text-sm text-gray-600">Total: {{ $requests->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Farmer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preferred Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Summary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $req->farmer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $req->farmer->phone ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($req->preferred_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $req->product_summary }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($req->status == 'pending') bg-vegigo-orange/20 text-vegigo-orange border border-vegigo-orange/30
                                @elseif($req->status == 'accepted') bg-vegigo-teal/20 text-vegigo-teal border border-vegigo-teal/30
                                @elseif($req->status == 'scheduled') bg-blue-100 text-blue-700 border border-blue-200
                                @elseif($req->status == 'rejected') bg-red-100 text-red-700 border border-red-200
                                @else bg-gray-100 text-gray-700 border border-gray-200
                                @endif">
                                {{ ucfirst($req->status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <form action="{{ route('admin.supply.update-status', $req) }}" method="POST" class="flex items-center space-x-2">
                                @csrf
                                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="pending" {{ $req->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="accepted" {{ $req->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                    <option value="scheduled" {{ $req->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="rejected" {{ $req->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                <button type="submit" class="px-4 py-2 bg-vegigo-green text-white rounded-lg hover:bg-emerald-600">
                                    Update
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No pickup requests found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </div>

    <div class="bg-teal-50 border border-teal-200 text-teal-900 rounded-lg p-4">
        <p class="text-sm">
            Yahan se aap farmer supply requests approve/reject kar sakte hain. Scheduling ke baad delivery team ko assign karen. Vendor allocation UI ko inventory page ke saath integrate kiya ja sakta hai.
        </p>
    </div>
</div>
@endsection

