<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public function checkout(Request $request, SaleService $saleService): RedirectResponse
    {
        $validated = $request->validate([
            'checkout_token' => ['required', 'string', 'size:64'],
            'customer_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'cash_received' => ['required', 'integer', 'min:0'],
        ]);

        $tokens = $request->session()->get('kasir.pos.checkout_tokens', []);
        $entry = $tokens[$validated['checkout_token']] ?? null;
        if (! is_array($entry) || ($entry['created_at'] ?? 0) < now()->subDay()->timestamp) {
            throw ValidationException::withMessages(['checkout_token' => 'Form checkout kedaluwarsa. Muat ulang halaman POS.']);
        }

        try {
            $order = $saleService->create(
                $validated['items'],
                (int) $request->user()->id,
                $entry['idempotency_key'],
                (int) $validated['cash_received'],
                $validated['customer_name'],
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
