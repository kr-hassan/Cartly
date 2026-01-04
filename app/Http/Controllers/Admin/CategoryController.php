<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = cache()->remember('admin.categories.index', 300, function () {
            return Category::with(['parent:id,name', 'children:id,name,parent_id'])
                ->select('id', 'name', 'slug', 'parent_id', 'is_active', 'created_at')
                ->latest()
                ->get();
        });
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $categories = cache()->remember('categories.all.select', 3600, function () {
            return Category::select('id', 'name', 'parent_id')->get();
        });
        return view('admin.categories.create', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        // Clear category caches
        cache()->forget('categories.all.select');
        cache()->forget('categories.all');
        cache()->forget('categories.active.root');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        $categories = cache()->remember('categories.all.except.' . $category->id, 3600, function () use ($category) {
            return Category::select('id', 'name', 'parent_id')
                ->where('id', '!=', $category->id)
                ->get();
        });
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        // Clear category caches
        cache()->forget('categories.all.select');
        cache()->forget('categories.all');
        cache()->forget('categories.active.root');
        cache()->forget('categories.active.select');
        cache()->forget('admin.categories.index');
        cache()->forget('categories.all.except.' . $category->id);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with products. Please remove products first.');
        }

        // Delete image
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        // Clear category caches
        cache()->forget('categories.all.select');
        cache()->forget('categories.all');
        cache()->forget('categories.active.root');
        cache()->forget('categories.active.select');
        cache()->forget('admin.categories.index');
        cache()->forget('categories.all.except.' . $category->id);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
