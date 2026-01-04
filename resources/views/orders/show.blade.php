@extends('layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2">Order #{{ $order->order_number }}</h1>
            <p class="text-gray-600">Placed on {{ $order->created_at->format('F d, Y h:i A') }}</p>
            <span class="inline-block mt-2 px-3 py-1 text-sm font-semibold rounded-full 
                {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }}
                {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        <!-- Order Items -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">Order Items</h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                    <div class="flex gap-4 border-b pb-4">
                        <div class="w-20 h-20 bg-gray-200 rounded flex-shrink-0"></div>
                        <div class="flex-1">
                            <h3 class="font-semibold">{{ $item->product_name }}</h3>
                            <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                            <p class="text-sm text-gray-600">Price: ${{ number_format($item->price, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">${{ number_format($item->total, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Shipping Information -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">Shipping Information</h2>
            <div class="bg-gray-50 p-4 rounded">
                <p class="font-semibold">{{ $order->customer_name }}</p>
                <p>{{ $order->shipping_address }}</p>
                @if($order->shipping_city)
                    <p>{{ $order->shipping_city }}{{ $order->shipping_postal_code ? ', ' . $order->shipping_postal_code : '' }}</p>
                @endif
                @if($order->customer_phone)
                    <p class="mt-2">Phone: {{ $order->customer_phone }}</p>
                @endif
                @if($order->customer_email)
                    <p>Email: {{ $order->customer_email }}</p>
                @endif
            </div>
        </div>

        <!-- Order Summary -->
        <div class="border-t pt-6">
            <div class="max-w-md ml-auto space-y-2">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>${{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount > 0)
                    <div class="flex justify-between text-red-600">
                        <span>Discount</span>
                        <span>-${{ number_format($order->discount, 2) }}</span>
                    </div>
                @endif
                @if($order->shipping_cost > 0)
                    <div class="flex justify-between">
                        <span>Shipping</span>
                        <span>${{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                @endif
                @if($order->tax > 0)
                    <div class="flex justify-between">
                        <span>Tax</span>
                        <span>${{ number_format($order->tax, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold text-xl border-t pt-2">
                    <span>Total</span>
                    <span>${{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

