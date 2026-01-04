@extends('layouts.admin')

@section('page-title', 'Tax Settings')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Tax Settings</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
    </div>

    <form action="{{ route('admin.tax-settings.update') }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-900">Tax Rate Information</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            Set the tax rate as a percentage (e.g., 10 for 10%, 0 for no tax). 
                            The tax will be calculated on: (Subtotal - Discount + Shipping Cost) × Tax Rate
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tax Rate (Percentage) *
                </label>
                <div class="relative">
                    <input type="number" 
                           name="tax_rate" 
                           value="{{ old('tax_rate', $taxSetting->tax_rate) }}" 
                           step="0.01" 
                           min="0" 
                           max="100" 
                           required
                           class="form-input pr-12"
                           placeholder="10.00">
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">%</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Enter the tax rate as a percentage (e.g., 10 for 10%, 0 for no tax, 15.5 for 15.5%)
                </p>
                @error('tax_rate')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Free Shipping Threshold (Optional)
                </label>
                <div class="relative">
                    <input type="number" 
                           name="free_shipping_threshold" 
                           value="{{ old('free_shipping_threshold', $taxSetting->free_shipping_threshold) }}" 
                           step="0.01" 
                           min="0" 
                           class="form-input pr-12"
                           placeholder="500.00">
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">৳</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Enter the minimum order amount (after discount) for free shipping (e.g., 500 for ৳500). Leave empty to disable free shipping.
                </p>
                @error('free_shipping_threshold')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="flex items-center cursor-pointer group mt-6">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', $taxSetting->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">Active</span>
                </label>
                <p class="text-sm text-gray-500 mt-1 ml-7">
                    When inactive, tax will not be calculated (effectively 0%)
                </p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Current Settings</h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Tax Rate:</strong> {{ number_format($taxSetting->tax_rate, 2) }}%</p>
                    <p><strong>Free Shipping Threshold:</strong> 
                        @if($taxSetting->free_shipping_threshold)
                            ৳{{ number_format($taxSetting->free_shipping_threshold, 2) }}
                        @else
                            <span class="text-gray-400">Not set</span>
                        @endif
                    </p>
                    <p><strong>Status:</strong> 
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $taxSetting->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $taxSetting->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="flex space-x-4 pt-4 border-t">
                <button type="submit" class="btn-primary">Update Tax Settings</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection

