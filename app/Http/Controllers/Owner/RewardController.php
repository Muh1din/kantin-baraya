<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Reward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function index(): View
    {
        return view('owner.rewards.index', [
            'rewards' => Reward::query()
                ->with('menu')
                ->withCount(['pointTransactions as redemptions_count' => fn ($query) => $query->where('type', 'redeemed')])
                ->latest()
                ->paginate(10),
            'menus' => Menu::query()->where('is_available', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Reward::query()->create($this->validatedData($request));

        return redirect()
            ->route('owner.rewards.index')
            ->with('status', 'Reward menu gratis berhasil ditambahkan.');
    }

    public function edit(Reward $reward): View
    {
        return view('owner.rewards.edit', [
            'reward' => $reward->load('menu'),
            'menus' => Menu::query()
                ->where('is_available', true)
                ->when($reward->menu_id, fn ($query) => $query->orWhere('id', $reward->menu_id))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, Reward $reward): RedirectResponse
    {
        $reward->update($this->validatedData($request));

        return redirect()
            ->route('owner.rewards.index')
            ->with('status', 'Reward berhasil diperbarui.');
    }

    public function destroy(Reward $reward): RedirectResponse
    {
        $reward->delete();

        return redirect()
            ->route('owner.rewards.index')
            ->with('status', 'Reward berhasil dihapus. Riwayat penukaran tetap tersimpan.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'menu_id' => ['required', 'integer', Rule::exists('menus', 'id')->where('is_available', true)],
            'points_required' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
