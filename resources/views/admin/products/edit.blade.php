@extends('layouts.admin')

@section('page-title', 'Edit Product')

@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-bold mb-6">Edit Product</h1>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('slug')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select name="category_id" required class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('price')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount Price</label>
                <input type="number" name="discount_price" step="0.01" value="{{ old('discount_price', $product->discount_price) }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('discount_price')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('stock_quantity')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('sku')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                <textarea name="short_description" rows="2"
                          class="w-full border border-gray-300 rounded-md px-3 py-2">{{ old('short_description', $product->short_description) }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="5"
                          class="w-full border border-gray-300 rounded-md px-3 py-2">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Add More Images</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
            </div>

            @if($product->images && count($product->images) > 0)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                    <div class="flex flex-wrap gap-4">
                        @foreach($product->images as $image)
                            <div class="relative">
                                <img src="{{ asset('storage/' . $image) }}" alt="" class="w-24 h-24 object-cover rounded">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                           class="rounded border-gray-300">
                    <span class="ml-2 text-sm text-gray-700">Featured</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Update Product
            </button>
            <a href="{{ route('admin.products.index') }}" class="bg-gray-200 text-gray-800 px-6 py-2 rounded hover:bg-gray-300">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

