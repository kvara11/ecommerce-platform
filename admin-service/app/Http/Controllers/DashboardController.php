<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status_id', 1)->count(),
            'total_revenue' => Order::where('payment_status_id', 2)->sum('total_amount'),
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_customers' => User::where('role_id', 2)->count(),
            'low_stock_products' => Product::whereHas('inventory', function ($query) {
                $query->where('quantity', '<', 10);
            })->count(),
        ];

        $recentOrders = Order::with('user', 'status', 'paymentStatus')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    
        $salesData = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->where('payment_status_id', 1)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = Product::withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take(5)
            ->get();
            
        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'recentOrders' => $recentOrders,
            'salesData' => $salesData,
            'topProducts' => $topProducts,
        ]);
    }
}
