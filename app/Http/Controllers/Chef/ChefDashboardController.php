<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChefDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'confirmed');
        abort_unless(in_array($status, ['confirmed', 'in_progress', 'completed'], true), 404);

        return view('chef.dashboard', [
            'status' => $status,
            'orders' => Order::query()->with(['items', 'payment'])->where('order_status', $status)->latest()->get(),
            'counts' => Order::query()->selectRaw('order_status, count(*) as aggregate')->whereIn('order_status', ['confirmed', 'in_progress', 'completed'])->groupBy('order_status')->pluck('aggregate', 'order_status'),
        ]);
    }

    public function startCooking(Request $request, int $order): RedirectResponse
    {
        $this->transition($request, $order, 'confirmed', 'in_progress', 'Pesanan mulai dimasak.');

        return back()->with('success', 'Pesanan mulai diproses.');
    }

    public function complete(Request $request, int $order): RedirectResponse
    {
        $this->transition($request, $order, 'in_progress', 'completed', 'Pesanan selesai disiapkan.');

        return back()->with('success', 'Pesanan selesai disiapkan.');
    }

    private function transition(Request $request, int $id, string $from, string $to, string $notes): void
    {
        DB::transaction(function () use ($request, $id, $from, $to, $notes): void {
            $order = Order::query()->lockForUpdate()->findOrFail($id);
            if (! in_array($order->order_channel, ['pos', 'online'], true) || $order->order_status !== $from) {
                throw ValidationException::withMessages(['order' => 'Status pesanan sudah berubah atau tidak dapat diproses.']);
            }

            $order->update(['order_status' => $to] + ($to === 'completed' ? ['completed_at' => now()] : []));
            $order->statusHistories()->create([
                'old_status' => $from,
                'new_status' => $to,
                'changed_by' => $request->user()->id,
                'notes' => $notes,
            ]);
        });
    }
}
