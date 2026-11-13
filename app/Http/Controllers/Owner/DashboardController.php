<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        $activeOrderStatuses = [
            'pending',
            'confirmed',
            'processing',
            'ready',
        ];

        $todayPayments = Payment::query()
            ->where('payment_status', 'paid')
            ->whereDate('paid_at', $today);

        return view('owner.dashboard', [
            'todaySales' => (clone $todayPayments)->sum('amount'),

            'activeOrderCount' => Order::query()
                ->whereIn('order_status', $activeOrderStatuses)
                ->count(),

            'activeMenuCount' => Menu::query()
                ->where('is_available', true)
                ->count(),

            'todayTransactionCount' => (clone $todayPayments)
                ->distinct()
                ->count('order_id'),

            'recentOrders' => Order::query()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get([
                    'id',
                    'order_code',
                    'customer_name',
                    'total_amount',
                    'order_status',
                    'created_at',
                ]),
        ]);
    }
}
