<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuLoyaltyPointController extends Controller
{
    public function index(): View
    {
        return view('owner.rewards.menu-points', [
            'menus' => Menu::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'points' => ['array'],
            'points.*' => ['integer', 'min:0', 'max:1000000'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['points'] as $menuId => $points) {
                Menu::query()->whereKey($menuId)->update(['points_per_item' => $points]);
            }
        });

        return redirect()
            ->route('owner.rewards.menu-points.index')
            ->with('status', 'Pengaturan poin per menu berhasil disimpan.');
    }
}
