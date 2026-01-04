@extends('layouts.admin')

@section('page-title', 'Create Coupon')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Create New Coupon</h1>

    <form action="{{ route('admin.coupons.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                <input type="text" name="code" value="{{ old('code') }}" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 font-mono uppercase">
                @error('code')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 rounded-md px-3 py-2">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                <select name="type" required class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                    <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                </select>
                @error('type')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Value *</label>
                <input type="number" name="value" step="0.01" value="{{ old('value') }}" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('value')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Purchase</label>
                    <input type="number" name="min_purchase" step="0.01" value="{{ old('min_purchase') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Discount</label>
                    <input type="number" name="max_discount" step="0.01" value="{{ old('max_discount') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usage Limit</label>
                <input type="number" name="usage_limit" value="{{ old('usage_limit') }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                <p class="text-sm text-gray-500 mt-1">Leave empty for unlimited usage</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valid From</label>
                    <input type="date" name="valid_from" value="{{ old('valid_from') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valid Until</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-gray-300">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Create Coupon
            </button>
            <a href="{{ route('admin.coupons.index') }}" class="bg-gray-200 text-gray-800 px-6 py-2 rounded hover:bg-gray-300">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

