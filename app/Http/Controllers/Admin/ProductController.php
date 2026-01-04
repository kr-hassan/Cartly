<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with('category:id,name,slug')
            ->select([
                'id', 'name', 'slug', 'category_id', 'price', 'discount_price', 
                'stock_quantity', 'sku', 'is_active', 'created_at'
            ]);

        // Search
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Status filter
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $products = $query->latest()->paginate(20);
        
        // Cache categories
        $categories = cache()->remember('categories.all', 3600, function () {
            return Category::select('id', 'name')->get();
        });

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = cache()->remember('categories.active.select', 3600, function () {
            return Category::active()->select('id', 'name', 'parent_id')->get();
        });
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
            $data['images'] = $images;
        }

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product = Product::create($data);

        // Clear product caches
        cache()->forget('products.featured');
        cache()->forget('products.trending');
        cache()->forget('admin.dashboard.top_products');

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'cartItems' => function($query) {
            $query->select('id', 'product_id', 'quantity')->limit(10);
        }, 'orderItems' => function($query) {
            $query->select('id', 'product_id', 'quantity', 'order_id')->limit(10);
        }]);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = cache()->remember('categories.active.select', 3600, function () {
            return Category::active()->select('id', 'name', 'parent_id')->get();
        });
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $images = $product->images ?? [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
            $data['images'] = $images;
        }

        // Generate slug if name changed and slug not provided
        if ($product->name !== $data['name'] && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);

        // Clear product caches
        cache()->forget('products.featured');
        cache()->forget('products.trending');
        cache()->forget('admin.dashboard.top_products');

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        // Delete images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        // Clear product caches
        cache()->forget('products.featured');
        cache()->forget('products.trending');
        cache()->forget('admin.dashboard.top_products');

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Delete product image.
     */
    public function deleteImage(Product $product, Request $request)
    {
        $request->validate([
            'image_path' => 'required|string',
        ]);

        $images = $product->images ?? [];
        $images = array_filter($images, fn($img) => $img !== $request->image_path);
        
        Storage::disk('public')->delete($request->image_path);
        $product->update(['images' => array_values($images)]);

        return back()->with('success', 'Image deleted successfully.');
    }
}
