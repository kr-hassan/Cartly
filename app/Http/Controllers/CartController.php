<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the cart.
     */
    public function index()
    {
        $cartTotals = $this->cartService->getCartTotals(auth()->user());
        
        return view('cart.index', compact('cartTotals'));
    }

    /**
     * Add item to cart.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        try {
            $this->cartService->addToCart(
                $request->product_id,
                $request->quantity ?? 1,
                auth()->user()
            );

            $cartCount = $this->cartService->getCartCount(auth()->user());

            // If AJAX request, return JSON response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully.',
                    'cart_count' => $cartCount,
                ]);
            }

            return redirect()->route('cart.index')
                ->with('success', 'Product added to cart successfully.');
        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        try {
            $this->cartService->updateCartItem(
                $id,
                $request->quantity,
                auth()->user()
            );

            return redirect()->route('cart.index')
                ->with('success', 'Cart updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove item from cart.
     */
    public function destroy($id)
    {
        try {
            $this->cartService->removeFromCart($id, auth()->user());

            return redirect()->route('cart.index')
                ->with('success', 'Item removed from cart.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get cart count (for AJAX requests).
     */
    public function count()
    {
        return response()->json([
            'count' => $this->cartService->getCartCount(auth()->user())
        ]);
    }
}
