<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
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
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with([
            'user:id,name,email,phone',
            'items' => function($query) {
                $query->select('id', 'order_id', 'product_id', 'quantity', 'price', 'total');
            },
            'items.product:id,name,slug'
        ])->select([
            'id', 'user_id', 'order_number', 'guest_name', 'guest_email', 
            'guest_phone', 'total', 'status', 'payment_status', 'created_at'
        ]);

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Payment status filter
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%")
                  ->orWhere('guest_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load('items.product', 'statusHistory.updater', 'user', 'coupon');
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the order status.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        try {
            $this->orderService->updateOrderStatus(
                $order,
                $request->status,
                $request->notes,
                auth()->user()
            );

            // Clear order-related caches
            cache()->forget('admin.dashboard.stats');
            cache()->forget('admin.dashboard.recent_orders');
            cache()->forget('admin.pending_orders_count');

            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display invoice for printing.
     */
    public function invoice(Order $order)
    {
        $order->load('items.product', 'user', 'coupon');
        return view('orders.invoice', compact('order'));
    }
}
