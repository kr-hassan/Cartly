@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Checkout</h1>
        <p class="text-gray-600">Complete your order with secure checkout</p>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="checkoutCoupon({{ $cartTotals['subtotal'] }}, '{{ route('checkout.validate-coupon') }}', '{{ route('checkout.calculate-shipping') }}', {{ $taxRate }}, {{ $freeShippingThreshold ? $freeShippingThreshold : 'null' }}, '{{ $currency->currency_symbol }}', '{{ $currency->currency_position }}')">
        @csrf

        <!-- Checkout Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Shipping Information -->
            <div class="card p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Shipping Information</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                               class="form-input"
                               placeholder="John Doe">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                               class="form-input"
                               placeholder="john@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Phone <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" required
                               class="form-input"
                               placeholder="+1 234 567 8900">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Shipping Address <span class="text-red-500">*</span>
                        </label>
                        <textarea name="shipping_address" rows="3" required
                                  class="form-input resize-none"
                                  placeholder="Street address, apartment, suite, etc.">{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            District <span class="text-red-500">*</span>
                        </label>
                        <select name="shipping_district" id="shipping_district" required
                                class="form-input"
                                @change="calculateShippingCost()">
                            <option value="">Select District</option>
                            @foreach($shippingSettings as $setting)
                                <option value="{{ $setting->district_name }}" {{ old('shipping_district') == $setting->district_name ? 'selected' : '' }}>
                                    {{ $setting->district_name }} - ৳{{ number_format($setting->shipping_cost, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('shipping_district')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                        <input type="text" name="shipping_city" value="{{ old('shipping_city') }}"
                               class="form-input"
                               placeholder="City name">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Postal Code</label>
                        <input type="text" name="shipping_postal_code" value="{{ old('shipping_postal_code') }}"
                               class="form-input"
                               placeholder="Postal code">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">State/Province</label>
                        <input type="text" name="shipping_state" value="{{ old('shipping_state') }}"
                               class="form-input"
                               placeholder="New York">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Country</label>
                        <input type="text" name="shipping_country" value="{{ old('shipping_country') }}"
                               class="form-input"
                               placeholder="United States">
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="card p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Payment Method</h2>
                </div>
                
                <div class="space-y-4">
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 transition-colors {{ old('payment_method', 'cod') == 'cod' ? 'border-blue-500 bg-blue-50' : '' }}">
                        <input type="radio" name="payment_method" value="cod" {{ old('payment_method', 'cod') == 'cod' ? 'checked' : '' }} required
                               class="w-5 h-5 text-blue-600 focus:ring-blue-500 focus:ring-2">
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">Cash on Delivery (COD)</p>
                                    <p class="text-sm text-gray-600">Pay when you receive your order</p>
                                </div>
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 transition-colors {{ old('payment_method') == 'online' ? 'border-blue-500 bg-blue-50' : '' }}">
                        <input type="radio" name="payment_method" value="online" {{ old('payment_method') == 'online' ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 focus:ring-blue-500 focus:ring-2">
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">Online Payment</p>
                                    <p class="text-sm text-gray-600">Pay securely with card or digital wallet</p>
                                </div>
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                        </div>
                    </label>
                </div>
                @error('payment_method')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Order Notes -->
            <div class="card p-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Notes (Optional)</h3>
                <textarea name="notes" rows="4"
                          class="form-input resize-none"
                          placeholder="Special delivery instructions, gift message, etc.">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="card p-6 sticky top-24">
                <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200">Order Summary</h2>
                
                <div class="space-y-4 mb-6 max-h-64 overflow-y-auto">
                    @foreach($cartTotals['items'] as $item)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gray-100">
                                @if($item->product->images && count($item->product->images) > 0)
                                    <img src="{{ asset('storage/' . $item->product->images[0]) }}" 
                                         alt="{{ $item->product->name }}" 
                                         class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                <p class="text-sm font-bold text-gray-900 mt-1">৳{{ number_format($item->subtotal, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Coupon Code Section -->
                <div class="border-t border-gray-200 pt-4 mb-4">
                    <div class="flex items-center space-x-2 mb-3">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-900">Have a coupon code?</h3>
                    </div>
                    <div class="flex space-x-2">
                        <input type="text" 
                               x-model="couponCode"
                               placeholder="Enter coupon code"
                               class="flex-1 form-input text-sm"
                               :disabled="couponApplied"
                               @keyup.enter="applyCoupon()">
                        <button type="button"
                                @click="applyCoupon()"
                                :disabled="loading || couponApplied || !couponCode"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-medium transition-colors">
                            <span x-show="!loading && !couponApplied">Apply</span>
                            <span x-show="loading">...</span>
                            <span x-show="couponApplied">✓</span>
                        </button>
                        <button type="button"
                                x-show="couponApplied"
                                @click="removeCoupon()"
                                class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition-colors">
                            Remove
                        </button>
                    </div>
                    <div x-show="error" class="mt-2 text-sm text-red-600" x-text="error"></div>
                    <div x-show="couponApplied && discount > 0" class="mt-2 text-sm text-green-600 font-medium">
                        Coupon applied! Discount: ৳<span x-text="parseFloat(discount).toFixed(2)"></span>
                    </div>
                    <input type="hidden" name="coupon_code" :value="couponCode" x-show="couponApplied">
                </div>

                <div class="border-t border-gray-200 pt-4 space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-semibold text-gray-900"><span x-text="formatCurrency({{ $cartTotals['subtotal'] }})"></span></span>
                    </div>
                    <div x-show="couponApplied && discount > 0" class="flex justify-between text-sm text-green-600">
                        <span>Discount</span>
                        <span class="font-semibold">-<span x-text="formatCurrency(discount)"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Shipping</span>
                        <span class="font-semibold text-gray-900">
                            <span x-show="freeShipping" class="text-green-600">Free</span>
                            <span x-show="!freeShipping && (!shippingCalculated || shippingCost == 0)" x-text="formatCurrency(0)"></span>
                            <span x-show="!freeShipping && shippingCalculated && shippingCost > 0" x-text="formatCurrency(getShippingCost())"></span>
                        </span>
                    </div>
                    <div x-show="freeShippingThreshold && !freeShipping" class="text-xs text-gray-500 mt-1">
                        Add <span x-text="formatCurrency(freeShippingThreshold - (subtotal - (couponApplied ? discount : 0)))"></span> more for free shipping!
                    </div>
                    <div class="flex justify-between text-sm" x-show="getTaxRateDisplay() > 0">
                        <span class="text-gray-600">Tax (<span x-text="getTaxRateDisplay().toFixed(2)"></span>%)</span>
                        <span class="font-semibold text-gray-900"><span x-text="formatCurrency(parseFloat(getTax()))"></span></span>
                    </div>
                </div>

                <div class="border-t-2 border-gray-900 pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900">Total</span>
                        <span class="text-2xl font-bold text-gray-900">
                            <span x-text="formatCurrency(parseFloat(getTotal()))"></span>
                        </span>
                    </div>
                </div>

                <button type="submit" 
                        class="btn-primary w-full py-4 text-lg mb-4">
                    Place Order
                </button>

                <a href="{{ route('cart.index') }}" 
                   class="block text-center text-sm text-gray-600 hover:text-blue-600 font-medium">
                    ← Back to Cart
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
