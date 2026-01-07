@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-blue-600 transition-colors">Products</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    @if($product->category)
                        <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="text-gray-500 hover:text-blue-600 transition-colors">{{ $product->category->name }}</a>
                    @endif
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="text-gray-700 font-medium">{{ $product->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="card overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">
            <!-- Product Images -->
            <div class="space-y-4">
                <div class="relative overflow-hidden rounded-2xl bg-gray-100 aspect-square">
                    @php
                        $productImages = $product->images;
                        if (is_string($productImages)) {
                            $productImages = json_decode($productImages, true);
                        }
                        // Clean up escaped slashes in image paths
                        if (is_array($productImages)) {
                            $productImages = array_map(function($img) {
                                return str_replace('\\/', '/', $img);
                            }, $productImages);
                        }
                    @endphp
                    @if($productImages && is_array($productImages) && count($productImages) > 0)
                        <img src="{{ asset('storage/' . $productImages[0]) }}" 
                             alt="{{ $product->name }}" 
                             id="main-image"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300">
                            <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif

                    @if($product->hasDiscount())
                        <div class="absolute top-4 left-4">
                            <span class="bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-bold px-4 py-2 rounded-full shadow-xl">
                                Save {{ $product->discount_percentage }}%
                            </span>
                        </div>
                    @endif
                </div>

                @if($productImages && is_array($productImages) && count($productImages) > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach($productImages as $index => $image)
                            <button onclick="document.getElementById('main-image').src='{{ asset('storage/' . $image) }}'" 
                                    class="aspect-square overflow-hidden rounded-lg border-2 border-transparent hover:border-blue-500 transition-colors">
                                <img src="{{ asset('storage/' . $image) }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Product Details -->
            <div class="flex flex-col">
                @if($product->category)
                    <a href="{{ route('products.index', ['category' => $product->category_id]) }}" 
                       class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold text-sm mb-4 group">
                        <svg class="w-4 h-4 mr-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        {{ $product->category->name }}
                    </a>
                @endif

                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>

                <!-- Price -->
                <div class="mb-6">
                    @if($product->hasDiscount())
                        <div class="flex items-baseline space-x-4 mb-2">
                            <span class="text-4xl font-bold text-gray-900">{{ $currency->formatAmount($product->discount_price) }}</span>
                            <span class="text-2xl text-gray-400 line-through">{{ $currency->formatAmount($product->price) }}</span>
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                                Save {{ $currency->formatAmount($product->price - $product->discount_price) }}
                            </span>
                        </div>
                    @else
                        <span class="text-4xl font-bold text-gray-900">{{ $currency->formatAmount($product->price) }}</span>
                    @endif
                </div>

                <!-- Stock Status -->
                <div class="mb-6 p-4 rounded-xl {{ $product->isInStock() ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <div class="flex items-center">
                        @if($product->isInStock())
                            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-green-900">In Stock</p>
                                <p class="text-sm text-green-700">{{ $product->stock_quantity }} units available</p>
                            </div>
                        @else
                            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-red-900">Out of Stock</p>
                                <p class="text-sm text-red-700">This item is currently unavailable</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if($product->sku)
                    <div class="mb-6">
                        <span class="text-sm text-gray-500">SKU:</span>
                        <span class="text-sm font-mono font-semibold text-gray-900 ml-2">{{ $product->sku }}</span>
                    </div>
                @endif

                <!-- Description -->
                @if($product->description)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                        <div class="text-gray-700 leading-relaxed prose max-w-none">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>
                @endif

                <!-- Wishlist Button (Visible for all users) -->
                <div class="mb-6" x-data="{ inWishlist: false, loading: false }" 
                     x-init="@auth if ($store.wishlist) { $store.wishlist.checkWishlist({{ $product->id }}); inWishlist = $store.wishlist.isInWishlist({{ $product->id }}); } @endauth">
                    <button @click="
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
                            class="w-full py-3 px-6 rounded-lg font-semibold transition-all flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="inWishlist ? 'bg-red-50 text-red-600 hover:bg-red-100 border-2 border-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-2 border-gray-200'">
                        <svg x-show="!loading && inWishlist" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        <svg x-show="!loading && !inWishlist" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Processing...' : (inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist')"></span>
                    </button>
                </div>

                <!-- Add to Cart -->
                @if($product->isInStock())
                    <div class="mt-auto" x-data="{ quantity: 1 }">
                        <div class="flex items-center space-x-4 mb-4">
                            <label class="text-sm font-semibold text-gray-700">Quantity:</label>
                            <input type="number" 
                                   x-model.number="quantity"
                                   min="1" 
                                   max="{{ $product->stock_quantity }}" 
                                   class="w-24 form-input text-center">
                        </div>
                        <button @click="addToCart({{ $product->id }}, quantity, $event)"
                                :disabled="loading"
                                class="btn-primary w-full py-4 text-lg flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loading ? 'Adding...' : 'Add to Cart'"></span>
                        </button>
                    </div>
                @else
                    <button disabled class="w-full py-4 text-lg bg-gray-300 text-gray-500 rounded-lg font-semibold cursor-not-allowed">
                        Out of Stock
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="product-card group cursor-pointer" onclick="window.location='{{ route('products.show', $relatedProduct->slug) }}'">
                        <div class="relative overflow-hidden rounded-t-xl bg-gray-100 aspect-square">
                            @php
                                $relatedImages = $relatedProduct->images;
                                if (is_string($relatedImages)) {
                                    $relatedImages = json_decode($relatedImages, true);
                                }
                                // Clean up escaped slashes in image paths
                                if (is_array($relatedImages)) {
                                    $relatedImages = array_map(function($img) {
                                        return str_replace('\\/', '/', $img);
                                    }, $relatedImages);
                                }
                            @endphp
                            @if($relatedImages && is_array($relatedImages) && count($relatedImages) > 0)
                                <img src="{{ asset('storage/' . $relatedImages[0]) }}" 
                                     alt="{{ $relatedProduct->name }}" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                {{ $relatedProduct->name }}
                            </h3>
                            <p class="text-xl font-bold text-gray-900">${{ number_format($relatedProduct->final_price, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
