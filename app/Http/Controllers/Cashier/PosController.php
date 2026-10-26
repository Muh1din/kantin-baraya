<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use LogicException;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        return view('cashier.pos.index', [
            'menus' => Menu::query()->where('is_available', true)->orderBy('name')->get(),
            'checkoutToken' => self::issueCheckoutToken($request),
        ]);
    }

    public function checkout(Request $request, OrderService $orderService): RedirectResponse
    {
        $validated = $request->validate([
            'checkout_token' => ['required', 'string', 'size:64'],
            'customer_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'cash_received' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['required', 'in:cash,qris'],
            'payment_timing' => ['required', 'in:now,after_meal'],
            'order_type' => ['required', 'in:makan_di_tempat,bungkus'],
        ]);

        $tokens = $request->session()->get('kasir.pos.checkout_tokens', []);
        $entry = $tokens[$validated['checkout_token']] ?? null;
        if (! is_array($entry) || ($entry['created_at'] ?? 0) < now()->subDay()->timestamp) {
            throw ValidationException::withMessages(['checkout_token' => 'Form checkout kedaluwarsa. Muat ulang halaman POS.']);
        }

        try {
            $order = $orderService->create(
                $validated['items'],
                (int) $request->user()->id,
                $entry['idempotency_key'],
                isset($validated['cash_received']) ? (int) $validated['cash_received'] : null,
                $validated['customer_name'],
                $validated['payment_method'],
                $validated['payment_timing'] === 'after_meal',
                $validated['order_type'],
            );
        } catch (InvalidArgumentException | LogicException $exception) {
            return back()->withErrors(['checkout' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('kasir.pos.receipt', $order)->with('status', 'Transaksi berhasil disimpan.');
    }

    public function history(Request $request): View
    {
        $filters = $request->validate([
            'invoice' => ['nullable', 'string', 'max:40'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $orders = Order::query()
            ->where('created_by', $request->user()->id)
            ->when($filters['invoice'] ?? null, fn($query, $invoice) => $query->where('order_code', 'like', '%' . $invoice . '%'))
            ->when($filters['date'] ?? null, fn($query, $date) => $query->whereDate('created_at', $date))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('cashier.pos.history', compact('orders', 'filters'));
    }

    public function receipt(Request $request, Order $order): View
    {
        abort_unless((int) $order->created_by === (int) $request->user()->id, 404);

        return view('cashier.pos.receipt', ['order' => $order->load(['items', 'payment'])]);
    }

    public function settle(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->created_by === (int) $request->user()->id && $order->order_channel === 'pos', 404);
        $validated = $request->validate([
            'cash_received' => ['nullable', 'integer', 'min:0'],
            'proof_image' => ['nullable', 'image', 'max:5120'],
            'manual_verification' => ['required', 'accepted'],
        ]);

        DB::transaction(function () use ($request, $order, $validated) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless((int) $order->created_by === (int) $request->user()->id && $order->order_channel === 'pos', 404);
            $payment = $order->payment()->lockForUpdate()->firstOrFail();
            if ($payment->payment_status !== 'pending') {
                throw ValidationException::withMessages(['payment' => 'Pembayaran sudah diproses.']);
            }

            if ($payment->payment_method === 'cash') {
                $request->validate(['cash_received' => ['required', 'integer', 'min:' . (int) $payment->amount]]);
                $payment->update([
                    'payment_status' => 'paid',
                    'cash_received' => (int) $validated['cash_received'],
                    'change_due' => (int) $validated['cash_received'] - (int) $payment->amount,
                    'paid_at' => now(),
                ]);
            } else {
                $request->validate(['proof_image' => ['required', 'image', 'max:5120']]);
                $proofPath = $request->file('proof_image')->store('payment-proofs/pos', 'public');
                $payment->update([
                    'payment_status' => 'paid',
                    'proof_image' => $proofPath,
                    'paid_at' => now(),
                ]);
            }

            if ($order->order_status === 'pending') {
                $order->update(['order_status' => 'confirmed']);
                $order->statusHistories()->create([
                    'old_status' => 'pending',
                    'new_status' => 'confirmed',
                    'changed_by' => $request->user()->id,
                    'notes' => 'Pembayaran POS diverifikasi kasir; pesanan diteruskan ke dapur.',
                ]);
            }
        });

        return redirect()->route('kasir.pos.receipt', $order)->with('status', 'Pembayaran pada transaksi ini berhasil diselesaikan.');
    }

    public static function issueCheckoutToken(Request $request): string
    {
        $tokens = $request->session()->get('kasir.pos.checkout_tokens', []);
        $cutoff = now()->subDay()->timestamp;
        $tokens = array_filter($tokens, fn($entry) => is_array($entry) && ($entry['created_at'] ?? 0) >= $cutoff);
        $token = Str::random(64);
        $tokens[$token] = [
            'idempotency_key' => 'pos:' . Str::uuid(),
            'created_at' => now()->timestamp,
        ];
        $request->session()->put('kasir.pos.checkout_tokens', $tokens);

        return $token;
    }
}
