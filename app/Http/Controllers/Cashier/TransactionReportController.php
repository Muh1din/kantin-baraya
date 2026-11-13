<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TransactionReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();
        $start = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        // Query orders directly and eager load one payment. This keeps the report
        // at one row per valid order even if the payment ledger contains retries.
        $baseQuery = Order::query()
            ->whereHas('payment')
            ->whereBetween('created_at', [$start, $end]);

        $query = (clone $baseQuery)->with([
            'payment' => fn ($paymentQuery) => $paymentQuery->orderByDesc('id')->limit(1),
        ]);

        if (! empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }

        $orders = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statuses = (clone $baseQuery)
            ->select('order_status')
            ->distinct()
            ->orderBy('order_status')
            ->pluck('order_status');

        return view('cashier.reports.transactions', [
            'orders' => $orders,
            'statuses' => $statuses,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $filters['status'] ?? '',
            ],
        ]);
    }
}
