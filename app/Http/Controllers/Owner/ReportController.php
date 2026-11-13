<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Services\MenuSalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function menuSales(Request $request, MenuSalesReportService $reportService): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();
        $report = $reportService->report($startDate, $endDate);

        return view('owner.reports.menu-sales', [
            ...$report,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function transactions(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();
        $start = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        $query = Order::query()
            ->with(['payment' => fn($query) => $query->orderByDesc('id')])
            ->whereBetween('created_at', [$start, $end]);

        if (! empty($validated['status'])) {
            $query->where('order_status', $validated['status']);
        }

        $orders = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statuses = Order::query()
            ->select('order_status')
            ->distinct()
            ->orderBy('order_status')
            ->pluck('order_status');

        return view('owner.reports.transactions', [
            'orders' => $orders,
            'statuses' => $statuses,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $validated['status'] ?? '',
            ],
        ]);
    }

    public function financial(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();
        $start = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        // A report treats each order as one transaction. If duplicate paid payment
        // rows exist for one order, use only its latest successful payment row.
        $latestSuccessfulPaymentIds = Payment::query()
            ->where('payment_status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw('MAX(id)')
            ->groupBy('order_id');

        $payments = Payment::query()->whereIn('id', $latestSuccessfulPaymentIds);

        $paymentBreakdown = (clone $payments)
            ->selectRaw('payment_method, COUNT(*) as transaction_count, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->orderBy('payment_method')
            ->get();

        return view('owner.reports.financial', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalRevenue' => (clone $payments)->sum('amount'),
            'transactionCount' => (clone $payments)->count(),
            'paymentBreakdown' => $paymentBreakdown,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('owner.report.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
