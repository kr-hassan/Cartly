<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        // Cache categories for 1 hour (they don't change often)
        $categories = cache()->remember('categories.active.root', 3600, function () {
            return Category::active()->root()->with('children')->get();
        });

        // Build cache key based on request parameters
        $cacheKey = 'products.index.' . md5(json_encode($request->all()));
        
        // Cache products listing for 5 minutes (faster page loads)
        $products = cache()->remember($cacheKey, 300, function () use ($request) {
            $query = Product::with('category')->active()->select([
                'id', 'name', 'slug', 'short_description', 'category_id', 
                'price', 'discount_price', 'stock_quantity', 'sku', 
                'images', 'is_active', 'created_at'
            ]);

            // Search - use fulltext if available, otherwise LIKE
            if ($request->has('search') && $request->search) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%");
                });
            }

            // Category filter
            if ($request->has('category') && $request->category) {
                $query->where('category_id', $request->category);
            }

            // Price filter - optimized
            if ($request->has('min_price') && $request->min_price) {
                $query->where(function ($q) use ($request) {
                    $q->where('discount_price', '>=', $request->min_price)
                      ->orWhere(function ($q2) use ($request) {
                          $q2->whereNull('discount_price')
                             ->where('price', '>=', $request->min_price);
                      });
                });
            }
            if ($request->has('max_price') && $request->max_price) {
                $query->where(function ($q) use ($request) {
                    $q->where(function ($q1) use ($request) {
                        $q1->whereNotNull('discount_price')
                           ->where('discount_price', '<=', $request->max_price);
                    })->orWhere(function ($q2) use ($request) {
                        $q2->whereNull('discount_price')
                           ->where('price', '<=', $request->max_price);
                    });
                });
            }

            // Stock filter
            if ($request->has('in_stock') && $request->in_stock) {
                $query->where('stock_quantity', '>', 0);
            }

            // Sort - optimized
            $sort = $request->get('sort', 'latest');
            switch ($sort) {
                case 'price_low':
                    $query->orderByRaw('COALESCE(discount_price, price) ASC')
                          ->orderBy('id', 'ASC');
                    break;
                case 'price_high':
                    $query->orderByRaw('COALESCE(discount_price, price) DESC')
                          ->orderBy('id', 'DESC');
                    break;
                case 'name':
                    $query->orderBy('name', 'ASC');
                    break;
                default:
                    $query->latest('id');
            }

            return $query->paginate(12)->withQueryString();
        });

        return response()
            ->view('products.index', compact('products', 'categories'))
            ->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * Display the specified product.
     */
    public function show($slug)
    {
        $product = Product::with('category')
            ->where('slug', $slug)
            ->active()
            ->select([
                'id', 'name', 'slug', 'short_description', 'description', 
                'category_id', 'price', 'discount_price', 'stock_quantity', 
                'sku', 'images', 'is_active', 'created_at'
            ])
            ->firstOrFail();
        
        // Get related products (same category) - optimized
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->select(['id', 'name', 'slug', 'price', 'discount_price', 'images'])
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
