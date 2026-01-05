<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display the user's wishlist.
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view your wishlist.');
        }

        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with('product.category')
            ->latest()
            ->paginate(20);

        return view('wishlist.index', compact('wishlistItems'));
    }

    /**
     * Add product to wishlist (AJAX).
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to add items to wishlist.',
                'requires_login' => true,
            ], 401);
        }

        // Check if product is already in wishlist
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already in your wishlist.',
                'in_wishlist' => true,
            ]);
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
        ]);

        $wishlistCount = Wishlist::where('user_id', $user->id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist successfully.',
            'wishlist_count' => $wishlistCount,
            'in_wishlist' => true,
        ]);
    }

    /**
     * Remove product from wishlist (AJAX).
     */
    public function destroy(Request $request, $id = null)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to remove items from wishlist.',
                'requires_login' => true,
            ], 401);
        }

        // If product_id is provided in request (AJAX), use that
        $productId = $request->input('product_id', $id);

        $wishlistItem = Wishlist::where('user_id', $user->id)
            ->where(function ($query) use ($productId, $id) {
                if ($productId) {
                    $query->where('product_id', $productId);
                } else {
                    $query->where('id', $id);
                }
            })
            ->first();

        if (!$wishlistItem) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found in wishlist.',
                ], 404);
            }
            return redirect()->route('wishlist.index')
                ->with('error', 'Product not found in wishlist.');
        }

        $wishlistItem->delete();

        $wishlistCount = Wishlist::where('user_id', $user->id)->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist successfully.',
                'wishlist_count' => $wishlistCount,
                'in_wishlist' => false,
            ]);
        }

        return redirect()->route('wishlist.index')
            ->with('success', 'Product removed from wishlist successfully.');
    }

    /**
     * Check if product is in wishlist (AJAX).
     */
    public function check(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'in_wishlist' => false,
            ]);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $inWishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->exists();

        return response()->json([
            'in_wishlist' => $inWishlist,
        ]);
    }

    /**
     * Get wishlist count (AJAX).
     */
    public function count()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'count' => 0,
            ]);
        }

        $count = Wishlist::where('user_id', $user->id)->count();

        return response()->json([
            'count' => $count,
        ]);
    }
}
