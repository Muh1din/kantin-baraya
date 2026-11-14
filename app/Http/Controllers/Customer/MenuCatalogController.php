<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Voucher;
use App\Services\OperationalSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $menuQuery = Menu::query()
            ->with('menuCategory')
            ->where('is_available', true)
            ->where('stock', '>', 0);

        if ($request->filled('kategori') && $request->query('kategori') !== 'all') {
            $menuQuery->where('category_id', $request->query('kategori'));
        }

        if ($request->filled('search')) {
            $menuQuery->where('name', 'like', '%' . $request->query('search') . '%');
        }

        return view('customer.menus.index', [
            'categories' => $categories,
            'menus' => $menuQuery->orderBy('name')->get(),
            'selectedCategory' => $request->query('kategori', 'all'),
            'searchQuery' => $request->query('search', ''),
            'mode' => $request->query('mode', 'dine-in'),
            'orderingClosed' => OperationalSchedule::isClosedToday(),
            'holidayToday' => OperationalSchedule::holidayToday(),
            'automaticVouchers' => Voucher::query()
                ->available()
                ->where('discount_type', 'percentage')
                ->withCount('orders')
                ->get()
                ->filter(fn (Voucher $voucher) => $voucher->usage_limit === null || $voucher->orders_count < $voucher->usage_limit)
                ->values(),
        ]);
    }
}
