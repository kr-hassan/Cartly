@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">My Wishlist</h1>
        <p class="text-gray-600">Save your favorite products for later</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if($wishlistItems->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($wishlistItems as $wishlistItem)
                @php
                    $product = $wishlistItem->product;
                    $hasDiscount = $product->discount_price !== null && $product->discount_price < $product->price;
                    $isInStock = $product->stock_quantity > 0;
                    $discountPercent = $hasDiscount ? round((($product->price - $product->discount_price) / $product->price) * 100, 0) : 0;
                    $firstImage = $product->images && count($product->images) > 0 ? asset('storage/' . $product->images[0]) : null;
                    $finalPrice = $hasDiscount ? $product->discount_price : $product->price;
                @endphp

                <div class="product-card bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group cursor-pointer" 
                     @click="window.location='{{ route('products.show', $product->slug) }}'"
                     x-data="{ loading: false }">
                    <!-- Product Image -->
                    <div class="relative overflow-hidden bg-gray-100 aspect-square">
                        @if($firstImage)
                            <img src="{{ $firstImage }}" 
                                 alt="{{ $product->name }}" 
                                 loading="lazy"
                                 decoding="async"
                                 width="200"
                                 height="200"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300" style="display: none;">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        
                        @if($hasDiscount)
                            <div class="absolute top-2 left-2">
                                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                    -{{ $discountPercent }}%
                                </span>
                            </div>
                        @endif

                        @if(!$isInStock)
                            <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                                <span class="bg-white text-gray-900 px-3 py-1 rounded text-sm font-semibold">Out of Stock</span>
                            </div>
                        @endif

                        <!-- Remove from Wishlist Button -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                            <button @click.stop="loading = true; removeFromWishlist({{ $wishlistItem->id }}, $event)"
                                    :disabled="loading"
                                    class="bg-white hover:bg-red-50 disabled:bg-gray-100 p-2 rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition-all disabled:cursor-not-allowed text-red-600">
                                <svg x-show="!loading" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                                <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Quick Add Button -->
                        @if($isInStock)
                            <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.stop="addToCart({{ $product->id }}, 1, $event)"
                                        :disabled="loading"
                                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white p-2 rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition-all disabled:cursor-not-allowed">
                                    <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <div class="p-3">
                        @if($product->category)
                            <p class="text-xs text-blue-600 font-semibold mb-1 uppercase tracking-wide truncate">
                                {{ $product->category->name }}
                            </p>
                        @endif
                        
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 text-sm group-hover:text-blue-600 transition-colors min-h-[2.5rem]">
                            {{ $product->name }}
                        </h3>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                @if($hasDiscount)
                                    <div class="flex items-baseline space-x-2">
                                        <span class="text-lg font-bold text-gray-900">${{ number_format($finalPrice, 2) }}</span>
                                        <span class="text-xs text-gray-500 line-through">${{ number_format($product->price, 2) }}</span>
                                    </div>
                                @else
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            {{ $wishlistItems->links() }}
        </div>
    @else
        <div class="card p-12 text-center">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Your wishlist is empty</h3>
            <p class="text-gray-600 mb-6">Start adding products you love to your wishlist!</p>
            <a href="{{ route('products.index') }}" class="btn-primary inline-block">
                Browse Products
            </a>
        </div>
    @endif
</div>

<script>
function removeFromWishlist(wishlistId, event) {
    if (event) {
        event.stopPropagation();
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) return;

    fetch(`/wishlist/${wishlistId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update wishlist
            window.location.reload();
        } else {
            alert(data.message || 'Failed to remove from wishlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to remove from wishlist. Please try again.');
    });
}
</script>
@endsection


