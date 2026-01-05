<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Coupon;
use App\Models\ShippingSetting;
use App\Models\TaxSetting;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class OrderService
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Create order from cart.
     */
    public function createOrder(array $data, $user = null): Order
    {
        $cartTotals = $this->cartService->getCartTotals($user);
        
        if ($cartTotals['item_count'] === 0) {
            throw new \Exception('Cart is empty');
        }

        // Validate and apply coupon if provided
        $coupon = null;
        $discount = 0;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if ($coupon && $coupon->isValid()) {
                $discount = $coupon->calculateDiscount($cartTotals['subtotal']);
            }
        }

        // Calculate shipping cost based on district
        $shippingCost = 0;
        $subtotal = $cartTotals['subtotal'];
        $orderAmountForShipping = $subtotal - $discount; // Order amount for free shipping check (subtotal - discount)
        
        // Check if order qualifies for free shipping
        if (!TaxSetting::qualifiesForFreeShipping($orderAmountForShipping)) {
            if (!empty($data['shipping_district'])) {
                $shippingCost = ShippingSetting::getShippingCost($data['shipping_district']);
            } elseif (!empty($data['shipping_city'])) {
                $shippingCost = ShippingSetting::getShippingCost($data['shipping_city']);
            } else {
                // Default shipping cost
                $shippingCost = ShippingSetting::getShippingCost('Outside Dhaka');
            }
        }
        // If qualifies for free shipping, $shippingCost remains 0

        // Calculate totals
        $subtotal = $cartTotals['subtotal'];
        $taxRate = TaxSetting::getTaxRate(); // Get dynamic tax rate (as decimal, e.g., 0.10 for 10%)
        $tax = ($subtotal - $discount + $shippingCost) * $taxRate;
        $total = $subtotal - $discount + $shippingCost + $tax;

        DB::beginTransaction();

        try {
            // Create order
            $order = Order::create([
                'user_id' => $user?->id,
                'guest_name' => $data['name'],
                'guest_email' => $data['email'] ?? null,
                'guest_phone' => $data['phone'],
                'shipping_address' => $data['shipping_address'],
                'shipping_city' => $data['shipping_city'] ?? null,
                'shipping_state' => $data['shipping_state'] ?? null,
                'shipping_district' => $data['shipping_district'] ?? null,
                'shipping_postal_code' => $data['shipping_postal_code'] ?? null,
                'shipping_country' => $data['shipping_country'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'total' => $total,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'payment_method' => $data['payment_method'] ?? 'cod',
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // Create order items and update stock
            foreach ($cartTotals['items'] as $cartItem) {
                $product = $cartItem->product;
                
                // Check stock availability
                if ($cartItem->quantity > $product->stock_quantity) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $cartItem->quantity,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'total' => $cartItem->price * $cartItem->quantity,
                ]);

                // Update product stock
                $product->decrement('stock_quantity', $cartItem->quantity);
            }

            // Update coupon usage
            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Create status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'notes' => 'Order created',
            ]);

            // Clear cart
            $this->cartService->clearCart($user);

            DB::commit();

            // Send notification to all admin users
            try {
                $adminUsers = User::where('role', 'admin')->get();
                foreach ($adminUsers as $admin) {
                    $admin->notify(new OrderPlacedNotification($order));
                }
            } catch (\Exception $e) {
                // Log error but don't fail the order
                \Log::error('Failed to send order placed notification: ' . $e->getMessage());
            }

            return $order->load('items.product');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Order $order, string $status, ?string $notes = null, $user = null): Order
    {
        if (!in_array($status, ['pending', 'processing', 'shipped', 'completed', 'cancelled'])) {
            throw new \Exception('Invalid order status');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $order->status;
            $order->update(['status' => $status]);

            // Create status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $status,
                'notes' => $notes ?? "Status changed from {$oldStatus} to {$status}",
                'updated_by' => $user?->id,
            ]);

            // If cancelled, restore stock
            if ($status === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }

                // Restore coupon usage if applicable
                if ($order->coupon_id) {
                    $order->coupon->decrement('used_count');
                }
            }

            DB::commit();

            // Send notification to customer when status changes
            try {
                // Get customer (user or guest)
                $customer = $order->user;
                $customerEmail = $order->customer_email;

                // If customer is a registered user, send notification
                if ($customer) {
                    $customer->notify(new OrderStatusChangedNotification($order, $oldStatus, $status));
                } elseif ($customerEmail) {
                    // For guest users, send email notification only
                    Notification::route('mail', $customerEmail)
                        ->notify(new OrderStatusChangedNotification($order, $oldStatus, $status));
                }
            } catch (\Exception $e) {
                // Log error but don't fail the status update
                \Log::error('Failed to send order status change notification: ' . $e->getMessage());
            }

            return $order->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get order by order number.
     */
    public function getOrderByNumber(string $orderNumber): Order
    {
        return Order::with([
            'items' => function($query) {
                $query->select('id', 'order_id', 'product_id', 'product_name', 'quantity', 'price', 'total');
            },
            'items.product:id,name,slug,images',
            'statusHistory:id,order_id,status,notes,created_at',
            'user:id,name,email',
            'coupon:id,code,type,value'
        ])
            ->where('order_number', $orderNumber)
            ->firstOrFail();
    }
}

