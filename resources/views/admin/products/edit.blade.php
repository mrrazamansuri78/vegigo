@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vegigo-green focus:border-vegigo-green">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                    <input type="text" name="category" value="{{ old('category', $product->category) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vegigo-green focus:border-vegigo-green">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Per Unit *</label>
                    <input type="number" step="0.01" name="price_per_unit" value="{{ old('price_per_unit', $product->price_per_unit) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vegigo-green focus:border-vegigo-green">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit *</label>
                    <select name="unit" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vegigo-green focus:border-vegigo-green">
                        <option value="kg" {{ $product->unit == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="g" {{ $product->unit == 'g' ? 'selected' : '' }}>Gram (g)</option>
                        <option value="piece" {{ $product->unit == 'piece' ? 'selected' : '' }}>Piece</option>
                        <option value="dozen" {{ $product->unit == 'dozen' ? 'selected' : '' }}>Dozen</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                    <input type="text" name="quantity" value="{{ old('quantity', $product->quantity) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vegigo-green focus:border-vegigo-green">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vegigo-green focus:border-vegigo-green">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
                    @if($product->image)
                        <div class="mb-2">
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover rounded">
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vegigo-green focus:border-vegigo-green">
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('admin.products.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-vegigo-green to-emerald-600 text-white rounded-lg hover:from-vegigo-green hover:to-emerald-700 shadow-md font-medium">
                    <i class="fas fa-save mr-2"></i>Update Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

