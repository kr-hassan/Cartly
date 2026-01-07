@extends('layouts.admin')

@section('page-title', 'Order ' . $order->order_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Order #{{ $order->order_number }}</h1>
                    <p class="text-gray-600">Placed on {{ $order->created_at->format('F d, Y h:i A') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.orders.invoice', $order) }}" 
                       target="_blank"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Invoice
                    </a>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full 
                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>

            <h2 class="text-xl font-bold mb-4">Order Items</h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                    <div class="flex justify-between border-b pb-4">
                        <div>
                            <h3 class="font-semibold">{{ $item->product_name }}</h3>
                            <p class="text-sm text-gray-600">Qty: {{ $item->quantity }} × {{ $currency->formatAmount($item->price) }}</p>
                        </div>
                        <div class="text-right font-semibold">
                            {{ $currency->formatAmount($item->total) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 pt-6 border-t">
                <div class="flex justify-end">
                    <div class="w-64 space-y-2">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>{{ $currency->formatAmount($order->subtotal) }}</span>
                        </div>
                        @if($order->discount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Discount:</span>
                                <span>-{{ $currency->formatAmount($order->discount) }}</span>
                            </div>
                        @endif
                        @if($order->shipping_cost > 0)
                            <div class="flex justify-between">
                                <span>Shipping:</span>
                                <span>{{ $currency->formatAmount($order->shipping_cost) }}</span>
                            </div>
                        @endif
                        @if($order->tax > 0)
                            <div class="flex justify-between">
                                <span>Tax:</span>
                                <span>{{ $currency->formatAmount($order->tax) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-bold text-lg pt-2 border-t">
                            <span>Total:</span>
                            <span>{{ $currency->formatAmount($order->total) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Shipping Information</h2>
            <div class="space-y-2">
                <p><strong>Name:</strong> {{ $order->customer_name }}</p>
                <p><strong>Email:</strong> {{ $order->customer_email ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                <p><strong>Address:</strong> {{ $order->shipping_address }}</p>
                @if($order->shipping_city)
                    <p>{{ $order->shipping_city }}{{ $order->shipping_postal_code ? ', ' . $order->shipping_postal_code : '' }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 sticky top-4">
            <h2 class="text-xl font-bold mb-4">Update Status</h2>
            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" required class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Update Status
                </button>
            </form>

            <div class="mt-6 pt-6 border-t">
                <h3 class="font-semibold mb-3">Status History</h3>
                <div class="space-y-2 text-sm">
                    @foreach($order->statusHistory as $history)
                        <div>
                            <div class="font-medium">{{ ucfirst($history->status) }}</div>
                            <div class="text-gray-600">{{ $history->created_at->format('M d, Y h:i A') }}</div>
                            @if($history->notes)
                                <div class="text-gray-500 italic">{{ $history->notes }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

