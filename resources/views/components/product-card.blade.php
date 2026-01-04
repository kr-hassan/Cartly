@php
    $hasDiscount = $product->discount_price !== null && $product->discount_price < $product->price;
    $isInStock = $product->stock_quantity > 0;
    $discountPercent = $hasDiscount ? round((($product->price - $product->discount_price) / $product->price) * 100, 0) : 0;
    $firstImage = $product->images && count($product->images) > 0 ? asset('storage/' . $product->images[0]) : null;
    $finalPrice = $hasDiscount ? $product->discount_price : $product->price;
@endphp

<div class="product-card bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group cursor-pointer" 
     @click="window.location='{{ route('products.show', $product->slug) }}'">
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

