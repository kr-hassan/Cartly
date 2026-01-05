@php
    $hasDiscount = $product->discount_price !== null && $product->discount_price < $product->price;
    $isInStock = $product->stock_quantity > 0;
    $discountPercent = $hasDiscount ? round((($product->price - $product->discount_price) / $product->price) * 100, 0) : 0;
    // Handle images - check if it's an array and has items
    $firstImage = null;
    if ($product->images && is_array($product->images) && count($product->images) > 0) {
        // Remove escaped slashes if present
        $imagePath = str_replace('\\/', '/', $product->images[0]);
        $firstImage = asset('storage/' . $imagePath);
    } elseif ($product->images && is_string($product->images)) {
        // Handle case where images might be stored as JSON string
        $decoded = json_decode($product->images, true);
        if (is_array($decoded) && count($decoded) > 0) {
            $imagePath = str_replace('\\/', '/', $decoded[0]);
            $firstImage = asset('storage/' . $imagePath);
        }
    }
    $finalPrice = $hasDiscount ? $product->discount_price : $product->price;
@endphp

<div class="product-card bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group cursor-pointer" 
     @click="window.location='{{ route('products.show', $product->slug) }}'"
     x-data="{ inWishlist: false, loading: false }"
     x-init="@auth if ($store.wishlist) { $store.wishlist.checkWishlist({{ $product->id }}); inWishlist = $store.wishlist.isInWishlist({{ $product->id }}); } @endauth">
    <!-- Product Image -->
    <div class="relative overflow-hidden bg-gray-100 aspect-square">
        @if($firstImage)
            <img src="{{ $firstImage }}" 
                 alt="{{ $product->name }}" 
                 loading="lazy"
                 decoding="async"
                 width="200"
                 height="200"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 relative z-0"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        @endif
        <!-- Placeholder (shown when no image or image fails to load) -->
        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300 absolute inset-0 z-0 {{ $firstImage ? 'hidden' : '' }}"
             style="{{ $firstImage ? '' : 'display: flex;' }}">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
        
        @if($hasDiscount)
            <div class="absolute top-2 left-2 z-20">
                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded shadow-lg">
                    -{{ $discountPercent }}%
                </span>
            </div>
        @endif

        <!-- Wishlist Button (Visible for all users) -->
        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-20">
            <button @click.stop="
                loading = true; 
                if ($store.wishlist) { 
                    $store.wishlist.toggleWishlist({{ $product->id }}, $event).then(() => { 
                        inWishlist = $store.wishlist.isInWishlist({{ $product->id }}); 
                        loading = false; 
                    }).catch((error) => { 
                        loading = false;
                        // If error is about login, modal will be shown by toggleWishlist
                    }); 
                } else {
                    loading = false;
                    window.dispatchEvent(new CustomEvent('open-login-modal'));
                }
            "
                    :disabled="loading"
                    class="bg-white hover:bg-red-50 disabled:bg-gray-100 p-2 rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition-all disabled:cursor-not-allowed"
                    :class="inWishlist ? 'text-red-600' : 'text-gray-600'">
                <svg x-show="!loading && inWishlist" 
                     class="w-5 h-5" 
                     fill="currentColor" 
                     viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <svg x-show="!loading && !inWishlist" 
                     class="w-5 h-5" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <svg x-show="loading" 
                     class="animate-spin w-5 h-5" 
                     fill="none" 
                     viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>

        @if(!$isInStock)
            <div class="absolute inset-0 bg-black/60 flex items-center justify-center z-30">
                <span class="bg-white text-gray-900 px-3 py-1 rounded text-sm font-semibold">Out of Stock</span>
            </div>
        @endif

        <!-- Quick Add Button -->
        @if($isInStock)
            <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-20">
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


