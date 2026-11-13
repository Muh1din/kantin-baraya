<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Services\MenuSalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MenuSalesReportController extends Controller
{
    public function index(Request $request, MenuSalesReportService $reportService): View
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        return view('cashier.reports.menu-sales', [
            ...$reportService->report($startDate, $endDate),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
