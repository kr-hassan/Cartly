<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get active banners (cached for 5 minutes)
        $banners = cache()->remember('banners.active', 300, function () {
            return Banner::active()->ordered()->get();
        });

        // Get featured categories
        $categories = cache()->remember('categories.active.root', 3600, function () {
            return Category::active()->root()->with('children')->limit(8)->get();
        });

        // Get featured products by category (for different sections)
        $featuredProducts = cache()->remember('products.featured', 300, function () {
            return Product::with('category')
                ->active()
                ->where('stock_quantity', '>', 0)
                ->select(['id', 'name', 'slug', 'short_description', 'category_id', 'price', 'discount_price', 'stock_quantity', 'images', 'is_active'])
                ->latest('id')
                ->limit(12)
                ->get();
        });

        // Get trending products
        $trendingProducts = cache()->remember('products.trending', 300, function () {
            return Product::with('category')
                ->active()
                ->where('stock_quantity', '>', 0)
                ->select(['id', 'name', 'slug', 'short_description', 'category_id', 'price', 'discount_price', 'stock_quantity', 'images', 'is_active'])
                ->orderByRaw('COALESCE(discount_price, price) ASC')
                ->limit(8)
                ->get();
        });

        // Get products by category for section display
        $productsByCategory = [];
        foreach ($categories->take(6) as $category) {
            $productsByCategory[$category->id] = Product::with('category')
                ->active()
                ->where('category_id', $category->id)
                ->where('stock_quantity', '>', 0)
                ->select(['id', 'name', 'slug', 'short_description', 'category_id', 'price', 'discount_price', 'stock_quantity', 'images', 'is_active'])
                ->limit(8)
                ->get();
        }

        return view('home', compact('banners', 'categories', 'featuredProducts', 'trendingProducts', 'productsByCategory'));
    }
}

