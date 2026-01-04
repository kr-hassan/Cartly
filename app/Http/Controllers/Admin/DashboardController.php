<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Cache stats for 5 minutes
        $stats = cache()->remember('admin.dashboard.stats', 300, function () {
            return [
                'total_orders' => Order::count(),
                'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total'),
                'total_customers' => User::where('role', 'customer')->count(),
                'total_products' => Product::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'processing_orders' => Order::where('status', 'processing')->count(),
            ];
        });

        // Recent orders - cached for 2 minutes
        $recentOrders = cache()->remember('admin.dashboard.recent_orders', 120, function () {
            return Order::with(['user:id,name,email', 'items' => function($query) {
                    $query->select('id', 'order_id', 'product_id', 'quantity', 'price', 'total');
                }, 'items.product' => function($query) {
                    $query->select('id', 'name', 'slug', 'images');
                }])
                ->select(['id', 'order_number', 'user_id', 'guest_name', 'total', 'status', 'created_at'])
                ->latest()
                ->limit(10)
                ->get();
        });

        // Revenue chart data (last 30 days) - cache for 1 hour
        $revenueData = cache()->remember('admin.dashboard.revenue', 3600, function () {
            return Order::where('status', '!=', 'cancelled')
                ->where('created_at', '>=', now()->subDays(30))
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as revenue')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        });

        // Top products - cache for 1 hour
        $topProducts = cache()->remember('admin.dashboard.top_products', 3600, function () {
            return Product::select([
                'products.id',
                'products.name',
                'products.slug',
                'products.price',
                'products.images',
                DB::raw('(SELECT COUNT(*) FROM order_items WHERE order_items.product_id = products.id) as order_items_count')
            ])
                ->orderBy('order_items_count', 'desc')
                ->limit(5)
                ->get();
        });

        return view('admin.dashboard', compact('stats', 'recentOrders', 'revenueData', 'topProducts'));
    }
}
