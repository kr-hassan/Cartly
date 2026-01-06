<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of user's orders.
     */
    public function index()
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to view your orders.');
        }

        $orders = $user->orders()
            ->with(['items' => function($query) {
                $query->select('id', 'order_id', 'product_id', 'quantity', 'price', 'total');
            }, 'items.product:id,name,slug,images'])
            ->select(['id', 'user_id', 'order_number', 'guest_name', 'total', 'status', 'created_at'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show($orderNumber)
    {
        try {
            $order = $this->orderService->getOrderByNumber($orderNumber);

            // Check authorization (user must own the order or be admin)
            $user = auth()->user();
            if (!$user || (!$user->isAdmin() && $order->user_id !== $user->id)) {
                // For guest orders, check if order number matches (simple security)
                // In production, you might want to add a token or email verification
                if (!$user && $order->user_id === null) {
                    // Allow guest to view their order
                } else {
                    abort(403, 'Unauthorized.');
                }
            }

            return view('orders.show', compact('order'));
        } catch (\Exception $e) {
            return redirect()->route('orders.index')
                ->with('error', 'Order not found.');
        }
    }

    /**
     * Cancel a pending order.
     */
    public function cancel($orderNumber)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Please login to cancel your order.');
            }

            $order = $this->orderService->getOrderByNumber($orderNumber);

            // Check authorization (user must own the order)
            if ($order->user_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }

            // Only pending orders can be cancelled
            if ($order->status !== 'pending') {
                return redirect()->route('orders.show', $order->order_number)
                    ->with('error', 'Only pending orders can be cancelled.');
            }

            // Cancel the order
            $this->orderService->updateOrderStatus(
                $order,
                'cancelled',
                'Order cancelled by customer',
                $user
            );

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Order has been cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->route('orders.index')
                ->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
}
