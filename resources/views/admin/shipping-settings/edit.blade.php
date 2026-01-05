@extends('layouts.admin')

@section('page-title', 'Edit Shipping Setting')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Edit Shipping Setting</h1>
        <a href="{{ route('admin.shipping-settings.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Shipping Settings</a>
    </div>

    <form action="{{ route('admin.shipping-settings.update', $shippingSetting) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">District Name *</label>
                <input type="text" name="district_name" value="{{ old('district_name', $shippingSetting->district_name) }}" required
                       class="form-input" placeholder="e.g., Dhaka, Outside Dhaka">
                @error('district_name')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Division (Optional)</label>
                <input type="text" name="division" value="{{ old('division', $shippingSetting->division) }}"
                       class="form-input" placeholder="e.g., Dhaka Division">
                @error('division')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Cost (Taka) *</label>
                <input type="number" name="shipping_cost" value="{{ old('shipping_cost', $shippingSetting->shipping_cost) }}" step="0.01" min="0" required
                       class="form-input">
                @error('shipping_cost')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $shippingSetting->sort_order) }}" min="0"
                           class="form-input">
                    @error('sort_order')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="flex items-center cursor-pointer group mt-6">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $shippingSetting->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex space-x-4 pt-4 border-t">
                <button type="submit" class="btn-primary">Update Shipping Setting</button>
                <a href="{{ route('admin.shipping-settings.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection


