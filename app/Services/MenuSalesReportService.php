<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class MenuSalesReportService
{
    /**
     * Build menu sales from unique orders with a valid, positive paid payment
     * recorded inside the requested period.
     *
     * @return array{menuSales: \Illuminate\Contracts\Pagination\LengthAwarePaginator, revenueTotal: float, quantityTotal: int}
     */
    public function report(string $startDate, string $endDate): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        $validOrderIds = Payment::query()
            ->where('payment_status', 'paid')
            ->where('amount', '>', 0)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->select('order_id')
            ->distinct();

        $menuSales = OrderItem::query()
            ->whereIn('order_id', $validOrderIds)
            ->select('menu_id', 'menu_name')
            ->selectRaw('SUM(quantity) as quantity_sold')
            ->selectRaw('SUM(subtotal) as revenue')
            ->groupBy('menu_id', 'menu_name')
            ->orderByDesc('revenue')
            ->orderBy('menu_name')
            ->paginate(20)
            ->withQueryString();

        $validItems = OrderItem::query()->whereIn('order_id', $validOrderIds);

        return [
            'menuSales' => $menuSales,
            'revenueTotal' => (float) (clone $validItems)->sum('subtotal'),
            'quantityTotal' => (int) (clone $validItems)->sum('quantity'),
        ];
    }
}
