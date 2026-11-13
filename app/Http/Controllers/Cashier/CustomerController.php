<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    private const CUSTOMER_ROLES = ['customer', 'pelanggan'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $latestPhone = Order::query()
            ->select('customer_phone')
            ->whereColumn('customer_id', 'users.id')
            ->whereNotNull('customer_phone')
            ->latest('created_at')
            ->latest('id')
            ->limit(1);

        $customers = User::query()
            ->select('users.*')
            ->selectSub($latestPhone, 'latest_order_phone')
            ->withCount('orders')
            ->withMax('orders', 'created_at')
            ->whereHas('role', fn ($query) => $query->whereIn('name', self::CUSTOMER_ROLES))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($customerQuery) use ($search): void {
                    $customerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('cashier.customers.index', compact('customers', 'filters'));
    }

    public function show(int $customer): View
    {
        $customerRecord = User::query()
            ->whereKey($customer)
            ->whereHas('role', fn ($query) => $query->whereIn('name', self::CUSTOMER_ROLES))
            ->firstOrFail();

        $phone = $customerRecord->orders()
            ->whereNotNull('customer_phone')
            ->latest('created_at')
            ->latest('id')
            ->value('customer_phone');

        $orders = $customerRecord->orders()
            ->with('payment')
            ->latest('created_at')
            ->paginate(10);

        return view('cashier.customers.show', [
            'customer' => $customerRecord,
            'phone' => $phone,
            'orders' => $orders,
        ]);
    }
}
