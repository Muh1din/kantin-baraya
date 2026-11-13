<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyAccount;
use App\Models\Reward;
use App\Services\RewardRedemptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoyaltyController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user();
        $account = LoyaltyAccount::query()->firstOrCreate(
            ['user_id' => $customer->id],
            ['saldo_poin' => 0],
        );

        return view('customer.loyalty.index', [
            'account' => $account,
            'rewards' => Reward::query()
                ->with('menu')
                ->where('is_active', true)
                ->whereHas('menu', fn ($query) => $query->where('is_available', true))
                ->orderBy('points_required')
                ->get(),
            'transactions' => $account->transactions()
                ->with(['reward.menu', 'order'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function redeem(Request $request, int $reward, RewardRedemptionService $redemptionService): RedirectResponse
    {
        $order = $redemptionService->redeem($request->user(), $reward);

        return redirect()
            ->route('customer.loyalty.index')
            ->with('status', 'Reward berhasil ditukar. Kode pesanan Anda: ' . $order->order_code);
    }
}
