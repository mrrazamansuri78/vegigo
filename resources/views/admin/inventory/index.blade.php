@extends('admin.layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory Management')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Total Stock</h3>
            <p class="text-3xl font-bold text-vegigo-teal">{{ number_format($totalStock, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6 md:col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Category-wise Stock</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($categoryTotals as $row)
                <div class="p-4 rounded-lg border border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">{{ ucfirst($row->category) }}</span>
                        <span class="text-vegigo-teal font-semibold">{{ number_format($row->total_quantity, 2) }}</span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500">No data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Products Stock</h3>
            <a href="{{ route('admin.products.create') }}" class="px-4 py-2 bg-vegigo-green text-white rounded-lg hover:bg-emerald-600">
                <i class="fas fa-plus mr-2"></i>Add Product
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Farmer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-900">{{ $product->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ number_format($product->quantity, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->unit }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->farmer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-vegigo-green hover:text-vegigo-teal font-semibold">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No products found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
    
    <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-lg p-4">
        <p class="text-sm">
            Vendor allocation & auto-distribution UI yahan integrate kiya ja sakta hai jab vendor entities available hon. Filhaal aap quantity edit karke stock manage kar sakte hain.
        </p>
    </div>
</div>
@endsection

