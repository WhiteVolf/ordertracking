<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    private function buildOrdersQuery(Request $request)
    {
        $ordersQuery = Order::query();

        if ($request->filled('from')) {
            $ordersQuery->whereDate('orders.created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $ordersQuery->whereDate('orders.created_at', '<=', $request->input('to'));
        }

        return $ordersQuery;
    }

    public function sales(Request $request)
    {
        $ordersQuery = $this->buildOrdersQuery($request);

        $productSales = $ordersQuery
            ->select('products.id as product_id', 'products.name')
            ->selectRaw('SUM(orders.quantity) as total_quantity')
            ->selectRaw('SUM(orders.amount) as total_amount')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name')
            ->get();

        return response()->json(['product_sales' => $productSales]);
    }

    public function orders(Request $request)
    {
        $ordersQuery = $this->buildOrdersQuery($request);

        $userOrders = $ordersQuery
            ->select('users.id as user_id', 'users.name')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(orders.amount) as total_amount')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name')
            ->get();

        return response()->json(['user_orders' => $userOrders]);
    }

    public function summary(Request $request)
    {
        $ordersQuery = $this->buildOrdersQuery($request);

        $productSales = (clone $ordersQuery)
            ->select('products.id as product_id', 'products.name')
            ->selectRaw('SUM(orders.quantity) as total_quantity')
            ->selectRaw('SUM(orders.amount) as total_amount')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name')
            ->get();

        $userOrders = (clone $ordersQuery)
            ->select('users.id as user_id', 'users.name')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(orders.amount) as total_amount')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name')
            ->get();

        return response()->json([
            'product_sales' => $productSales,
            'user_orders' => $userOrders,
        ]);
    }
}
