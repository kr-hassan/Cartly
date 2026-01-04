@extends('layouts.app')

@section('title', 'Home - ' . config('app.name', 'Cartly'))

@section('content')
<!-- Hero Banner Section with Slider -->
@if($banners->count() > 0)
<section class="relative mb-8 overflow-hidden" x-data="bannerSlider({{ $banners->count() }})">
    <div class="relative h-[400px] md:h-[500px]">
        @foreach($banners as $index => $banner)
            <div x-show="currentSlide === {{ $index }}" 
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-gradient-to-r {{ $banner->background_color }} text-white overflow-hidden">
                @if($banner->images && count($banner->images) > 0)
                    <!-- Banner Images as Background -->
                    <div class="absolute inset-0">
                        @foreach($banner->images as $imageIndex => $image)
                            <img src="{{ asset('storage/' . $image) }}" 
                                 alt="{{ $banner->title }}"
                                 class="absolute inset-0 w-full h-full object-cover {{ $imageIndex === 0 ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-500">
                        @endforeach
                        <!-- Overlay for better text readability -->
                        <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-black/20 to-transparent"></div>
                    </div>
                @endif
                
                <!-- Content Overlay -->
                <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
                    <div class="flex items-center h-full">
                        <div class="w-full lg:w-1/2 text-center lg:text-left">
                            @if($banner->title)
                                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">{{ $banner->title }}</h1>
                            @endif
                            @if($banner->subtitle)
                                <p class="text-lg sm:text-xl md:text-2xl mb-6 opacity-95 drop-shadow-md">{{ $banner->subtitle }}</p>
                            @endif
                            @if($banner->button_text)
                                <a href="{{ $banner->button_link ?? route('products.index') }}" 
                                   class="inline-block bg-white text-gray-900 px-6 sm:px-8 py-3 rounded-lg font-bold text-base sm:text-lg hover:bg-gray-100 transition-colors shadow-xl">
                                    {{ $banner->button_text }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Navigation Arrows -->
    @if($banners->count() > 1)
    <button @click="previousSlide()" 
            class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white p-3 rounded-full transition-all backdrop-blur-sm">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    <button @click="nextSlide()" 
            class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white p-3 rounded-full transition-all backdrop-blur-sm">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>

    <!-- Dots Indicator -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
        @foreach($banners as $index => $banner)
            <button @click="goToSlide({{ $index }})"
                    :class="currentSlide === {{ $index }} ? 'bg-white' : 'bg-white/50'"
                    class="w-3 h-3 rounded-full transition-all"></button>
        @endforeach
    </div>
    @endif
</section>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Featured Products Section -->
    @if($featuredProducts->count() > 0)
    <section class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Featured Products</h2>
            <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-2">
                See all
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @foreach($featuredProducts->take(12) as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
    @endif

    <!-- Products by Category Sections -->
    @foreach($categories->take(6) as $category)
        @if(isset($productsByCategory[$category->id]) && $productsByCategory[$category->id]->count() > 0)
        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $category->name }}</h2>
                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-2">
                    See all
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach($productsByCategory[$category->id]->take(6) as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
        @endif
    @endforeach

    <!-- Trending Products Section -->
    @if($trendingProducts->count() > 0)
    <section class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Trending Now</h2>
            <a href="{{ route('products.index', ['sort' => 'price_low']) }}" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-2">
                See all
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @foreach($trendingProducts as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection

