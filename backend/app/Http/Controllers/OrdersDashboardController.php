<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrdersDashboardController extends Controller
{
    public function index()
    {
        $orders = collect();
        $todayOrders = collect();
        $deliveredOrders = collect();
        $pendingOrders = collect();
        $issuesCount = 0;

        $paymentBreakdown = [
            ['label' => 'Paid', 'count' => 0, 'tone' => 'green'],
            ['label' => 'Pending', 'count' => 0, 'tone' => 'amber'],
            ['label' => 'Failed', 'count' => 0, 'tone' => 'red'],
            ['label' => 'Refunded', 'count' => 0, 'tone' => 'slate'],
        ];

        $fulfillmentQueues = [
            ['label' => 'Ready to pack', 'count' => 0, 'icon' => 'package', 'tone' => 'amber'],
            ['label' => 'In processing', 'count' => 0, 'icon' => 'loader-circle', 'tone' => 'blue'],
            ['label' => 'Out for shipping', 'count' => 0, 'icon' => 'truck', 'tone' => 'indigo'],
            ['label' => 'Delivered', 'count' => 0, 'icon' => 'badge-check', 'tone' => 'green'],
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'totalOrders' => $orders->count(),
            'todayOrderCount' => $todayOrders->count(),
            'pendingOrdersCount' => $pendingOrders->count(),
            'deliveredOrdersCount' => $deliveredOrders->count(),
            'issuesCount' => $issuesCount,
            'totalRevenueLabel' => 'UGX 0',
            'todayRevenueLabel' => 'UGX 0',
            'paymentBreakdown' => $paymentBreakdown,
            'fulfillmentQueues' => $fulfillmentQueues,
            'recentEvents' => collect(),
            'profile' => session('admin_profile'),
        ]);
    }

    public function show(string $id)
    {
        abort(404);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        return response()->json(['error' => 'Order not found.'], 404);
    }
}
