@extends('layouts.admin')

@section('page-title', 'Currency Settings')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Currency Settings</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
    </div>

    <form action="{{ route('admin.currency-settings.update') }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-900">Currency Information</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            Configure the currency symbol and format used throughout the store. 
                            Common examples: USD ($), BDT (৳), EUR (€), GBP (£)
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Currency Code *
                </label>
                <input type="text" 
                       name="currency_code" 
                       value="{{ old('currency_code', $currencySetting->currency_code) }}" 
                       maxlength="3"
                       required
                       class="form-input uppercase"
                       placeholder="USD">
                <p class="text-sm text-gray-500 mt-1">
                    ISO 4217 currency code (e.g., USD, BDT, EUR, GBP)
                </p>
                @error('currency_code')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Currency Symbol *
                </label>
                <input type="text" 
                       name="currency_symbol" 
                       value="{{ old('currency_symbol', $currencySetting->currency_symbol) }}" 
                       maxlength="10"
                       required
                       class="form-input"
                       placeholder="$">
                <p class="text-sm text-gray-500 mt-1">
                    The symbol to display (e.g., $, ৳, €, £)
                </p>
                @error('currency_symbol')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Currency Name *
                </label>
                <input type="text" 
                       name="currency_name" 
                       value="{{ old('currency_name', $currencySetting->currency_name) }}" 
                       maxlength="50"
                       required
                       class="form-input"
                       placeholder="US Dollar">
                <p class="text-sm text-gray-500 mt-1">
                    Full name of the currency (e.g., US Dollar, Bangladeshi Taka, Euro)
                </p>
                @error('currency_name')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Currency Position *
                </label>
                <select name="currency_position" 
                        required
                        class="form-select">
                    <option value="before" {{ old('currency_position', $currencySetting->currency_position) === 'before' ? 'selected' : '' }}>
                        Before amount (e.g., $100.00)
                    </option>
                    <option value="after" {{ old('currency_position', $currencySetting->currency_position) === 'after' ? 'selected' : '' }}>
                        After amount (e.g., 100.00 $)
                    </option>
                </select>
                <p class="text-sm text-gray-500 mt-1">
                    Choose where to display the currency symbol relative to the amount
                </p>
                @error('currency_position')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="flex items-center cursor-pointer group mt-6">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', $currencySetting->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">Active</span>
                </label>
                <p class="text-sm text-gray-500 mt-1 ml-7">
                    When inactive, default currency (USD $) will be used
                </p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Current Settings</h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Currency Code:</strong> {{ $currencySetting->currency_code }}</p>
                    <p><strong>Currency Symbol:</strong> {{ $currencySetting->currency_symbol }}</p>
                    <p><strong>Currency Name:</strong> {{ $currencySetting->currency_name }}</p>
                    <p><strong>Position:</strong> {{ ucfirst($currencySetting->currency_position) }} amount</p>
                    <p><strong>Example:</strong> 
                        @if($currencySetting->currency_position === 'before')
                            {{ $currencySetting->currency_symbol }}100.00
                        @else
                            100.00 {{ $currencySetting->currency_symbol }}
                        @endif
                    </p>
                    <p><strong>Status:</strong> 
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $currencySetting->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $currencySetting->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="flex space-x-4 pt-4 border-t">
                <button type="submit" class="btn-primary">Update Currency Settings</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection

