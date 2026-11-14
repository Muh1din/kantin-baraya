<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\RecipeInventoryService;
use DomainException;
use Illuminate\View\View;

class ChefDashboardController extends Controller
{
    public function index(Request $request)
    {

        $counts = [
            'confirmed' => Order::where('order_status', 'confirmed')->count(),
            'in_progress' => Order::where('order_status', 'in_progress')->count(),
            'completed' => Order::where('order_status', 'completed')->count(),
        ];

        $status = $request->get('status', 'confirmed');
        $knownConfirmedOrderIds = Order::where('order_status', 'confirmed')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $orders = Order::whereIn('order_status', ['confirmed', 'in_progress', 'completed'])
            ->where('order_status', $status)
            ->with(['items.menu', 'statusHistories'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('chef.dashboard', compact('orders', 'status', 'counts', 'knownConfirmedOrderIds'));
    }

    public function startCooking(Order $order, RecipeInventoryService $inventoryService)
    {
        $error = null;

        try {
            DB::transaction(function () use ($order, $inventoryService, &$error) {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                if ($lockedOrder->order_status !== 'confirmed') {
                    $error = 'Pesanan tidak dalam status confirmed.';

                    return;
                }

                $inventoryService->consumeForOrder($lockedOrder, Auth::id());
                $lockedOrder->update(['order_status' => 'in_progress']);
                $lockedOrder->statusHistories()->create([
                    'old_status' => 'confirmed',
                    'new_status' => 'in_progress',
                    'changed_by' => Auth::id(),
                    'notes' => 'Chef mulai mengerjakan pesanan.',
                ]);
            });
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if ($error) {
            return back()->with('error', $error);
        }

        return back()->with('success', 'Pesanan mulai dikerjakan.');
    }

    public function cancelCooking(Order $order, RecipeInventoryService $inventoryService)
    {
        $error = null;

        try {
            DB::transaction(function () use ($order, $inventoryService, &$error) {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                if ($lockedOrder->order_status !== 'in_progress') {
                    $error = 'Hanya pesanan yang sedang diproses yang dapat dibatalkan dari antrean Koki.';

                    return;
                }

                $inventoryService->restoreForCancelledOrder($lockedOrder, Auth::id());
                $lockedOrder->update(['order_status' => 'cancelled']);
                $lockedOrder->statusHistories()->create([
                    'old_status' => 'in_progress',
                    'new_status' => 'cancelled',
                    'changed_by' => Auth::id(),
                    'notes' => 'Pesanan dibatalkan Koki; bahan baku yang sudah dipakai dikembalikan ke stok.',
                ]);
            });
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if ($error) {
            return back()->with('error', $error);
        }

        return back()->with('success', 'Pesanan dibatalkan dan bahan baku yang dipakai sudah dikembalikan.');
    }

    public function complete(Order $order)
    {
        if ($order->order_status !== 'in_progress') {
            return back()->with('error', 'Pesanan tidak dalam status in_progress.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => 'completed',
                'completed_at' => now(),
            ]);
            $order->statusHistories()->create([
                'old_status' => 'in_progress',
                'new_status' => 'completed',
                'changed_by' => Auth::id(),
                'notes' => 'Pesanan selesai',
            ]);
        });

        return back()->with('success', 'Pesanan selesai.');
    }

    public function checkNewOrders(Request $request)
    {
        $newOrders = Order::where('order_status', 'confirmed')
            ->with(['items.menu'])
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'new_orders' => $newOrders->map(fn (Order $order) => [
                'id' => $order->id,
                'status' => $order->order_status,
                'html' => view('chef.orders.partials.dashboard-row', [
                    'order' => $order,
                    'statusLabels' => [
                        'confirmed' => 'Menunggu Diproses',
                        'in_progress' => 'Sedang Dimasak',
                        'completed' => 'Selesai',
                    ],
                    'statusStyles' => [
                        'confirmed' => 'bg-amber-50 text-amber-700 ring-amber-200',
                        'in_progress' => 'bg-orange-50 text-orange-700 ring-orange-200',
                        'completed' => 'bg-green-50 text-green-700 ring-green-200',
                    ],
                ])->render(),
            ])->values(),
            'count' => $newOrders->count(),
        ]);
    }
}
