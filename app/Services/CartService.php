<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Get the current session ID for guest carts.
     */
    protected function getSessionId(): string
    {
        if (!Session::has('cart_session_id')) {
            Session::put('cart_session_id', Str::uuid()->toString());
        }
        
        return Session::get('cart_session_id');
    }

    /**
     * Get cart items for the current user or guest.
     */
    public function getCartItems($user = null): \Illuminate\Database\Eloquent\Collection
    {
        if ($user) {
            return Cart::with(['product' => function($query) {
                    $query->select('id', 'name', 'slug', 'price', 'discount_price', 'stock_quantity', 'images', 'category_id');
                }, 'product.category:id,name,slug'])
                ->select('id', 'user_id', 'product_id', 'quantity', 'price', 'created_at')
                ->where('user_id', $user->id)
                ->get();
        }

        $sessionId = $this->getSessionId();
        return Cart::with(['product' => function($query) {
                $query->select('id', 'name', 'slug', 'price', 'discount_price', 'stock_quantity', 'images', 'category_id');
            }, 'product.category:id,name,slug'])
            ->select('id', 'session_id', 'product_id', 'quantity', 'price', 'created_at')
            ->where('session_id', $sessionId)
            ->get();
    }

    /**
     * Add item to cart.
     */
    public function addToCart(int $productId, int $quantity = 1, $user = null): Cart
    {
        $product = Product::findOrFail($productId);

        if (!$product->is_active || !$product->isInStock()) {
            throw new \Exception('Product is not available');
        }

        if ($quantity > $product->stock_quantity) {
            throw new \Exception('Insufficient stock');
        }

        $cartData = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $product->final_price,
        ];

        if ($user) {
            $cartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                if ($newQuantity > $product->stock_quantity) {
                    throw new \Exception('Insufficient stock');
                }
                $cartItem->update(['quantity' => $newQuantity]);
                return $cartItem;
            }

            $cartData['user_id'] = $user->id;
        } else {
            $sessionId = $this->getSessionId();
            $cartItem = Cart::where('session_id', $sessionId)
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                if ($newQuantity > $product->stock_quantity) {
                    throw new \Exception('Insufficient stock');
                }
                $cartItem->update(['quantity' => $newQuantity]);
                return $cartItem;
            }

            $cartData['session_id'] = $sessionId;
        }

        return Cart::create($cartData);
    }

    /**
     * Update cart item quantity.
     */
    public function updateCartItem(int $cartId, int $quantity, $user = null): Cart
    {
        $query = Cart::with('product')->where('id', $cartId);

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('session_id', $this->getSessionId());
        }

        $cartItem = $query->firstOrFail();

        if ($quantity > $cartItem->product->stock_quantity) {
            throw new \Exception('Insufficient stock');
        }

        if ($quantity <= 0) {
            $cartItem->delete();
            return $cartItem;
        }

        $cartItem->update(['quantity' => $quantity]);
        return $cartItem;
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(int $cartId, $user = null): bool
    {
        $query = Cart::where('id', $cartId);

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('session_id', $this->getSessionId());
        }

        return $query->delete();
    }

    /**
     * Clear cart.
     */
    public function clearCart($user = null): bool
    {
        if ($user) {
            return Cart::where('user_id', $user->id)->delete();
        }

        return Cart::where('session_id', $this->getSessionId())->delete();
    }

    /**
     * Get cart totals.
     */
    public function getCartTotals($user = null): array
    {
        $items = $this->getCartItems($user);
        
        $subtotal = $items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total_items' => $items->sum('quantity'),
            'item_count' => $items->count(),
        ];
    }

    /**
     * Get cart item count.
     */
    public function getCartCount($user = null): int
    {
        if ($user) {
            return Cart::where('user_id', $user->id)->sum('quantity');
        }

        return Cart::where('session_id', $this->getSessionId())->sum('quantity');
    }

    /**
     * Merge guest cart with user cart after login.
     */
    public function mergeGuestCart($user): void
    {
        $sessionId = $this->getSessionId();
        $guestCartItems = Cart::where('session_id', $sessionId)->get();

        foreach ($guestCartItems as $guestItem) {
            $existingItem = Cart::where('user_id', $user->id)
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $guestItem->quantity;
                $product = $guestItem->product;
                
                if ($newQuantity <= $product->stock_quantity) {
                    $existingItem->update(['quantity' => $newQuantity]);
                }
                
                $guestItem->delete();
            } else {
                $guestItem->update([
                    'user_id' => $user->id,
                    'session_id' => null,
                ]);
            }
        }

        Session::forget('cart_session_id');
    }
}

