@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Shopping Cart</h1>
        <p class="text-gray-600">Review your items before checkout</p>
    </div>

    @if($cartTotals['item_count'] > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                @foreach($cartTotals['items'] as $item)
                    <div class="card overflow-hidden">
                        <div class="p-6 flex flex-col sm:flex-row gap-6">
                            <!-- Product Image -->
                            <div class="flex-shrink-0">
                                <a href="{{ route('products.show', $item->product->slug) }}" class="block w-32 h-32 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-gray-100">
                                    @if($item->product->images && count($item->product->images) > 0)
                                        <img src="{{ asset('storage/' . $item->product->images[0]) }}" 
                                             alt="{{ $item->product->name }}" 
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                            </div>
                            
                            <!-- Product Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <a href="{{ route('products.show', $item->product->slug) }}" class="text-lg font-bold text-gray-900 hover:text-blue-600 transition-colors mb-2 block">
                                            {{ $item->product->name }}
                                        </a>
                                        <p class="text-gray-600 text-sm mb-4">${{ number_format($item->price, 2) }} each</p>
                                        
                                        <!-- Quantity Control -->
                                        <div class="flex items-center space-x-4" x-data="{ quantity: {{ $item->quantity }}, updating: false }">
                                            <form action="{{ route('cart.update', $item->id) }}" 
                                                  method="POST" 
                                                  class="flex items-center space-x-2"
                                                  @submit.prevent="updating = true; $event.target.submit();">
                                                @csrf
                                                @method('PUT')
                                                <label class="text-sm font-semibold text-gray-700">Qty:</label>
                                                <input type="number" 
                                                       name="quantity" 
                                                       x-model.number="quantity"
                                                       min="1" 
                                                       max="{{ $item->product->stock_quantity }}"
                                                       class="w-20 form-input text-center"
                                                       @change="$event.target.form.submit()">
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Price & Actions -->
                                    <div class="flex flex-col items-end space-y-4">
                                        <div class="text-right">
                                            <p class="text-2xl font-bold text-gray-900">${{ number_format($item->subtotal, 2) }}</p>
                                            <p class="text-sm text-gray-500">${{ number_format($item->price, 2) }} × {{ $item->quantity }}</p>
                                        </div>
                                        
                                        <form action="{{ route('cart.destroy', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors flex items-center space-x-1 text-sm font-semibold">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                <span>Remove</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="card p-6 sticky top-24">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200">Order Summary</h2>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal ({{ $cartTotals['total_items'] }} items)</span>
                            <span class="font-semibold text-gray-900">${{ number_format($cartTotals['subtotal'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-semibold text-gray-900">Calculated at checkout</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-900">Total</span>
                            <span class="text-2xl font-bold text-gray-900">${{ number_format($cartTotals['subtotal'], 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" 
                       class="btn-primary w-full text-center mb-4">
                        Proceed to Checkout
                    </a>

                    <a href="{{ route('products.index') }}" 
                       class="block w-full text-center px-4 py-3 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg font-semibold transition-colors">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="card p-16 text-center">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Your cart is empty</h3>
            <p class="text-gray-600 mb-8">Start adding items to your cart to continue shopping.</p>
            <a href="{{ route('products.index') }}" class="btn-primary inline-block">
                Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection
