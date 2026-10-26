<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $role = match ($request->user()->role?->name) {
            'cashier' => 'kasir',
            'customer' => 'pelanggan',
            'chef' => 'koki',
            default => $request->user()->role?->name,
        };

        if ($role === 'owner') {
            return view('owner.dashboard', [
                'activeMenuCount' => Menu::query()->where('is_available', true)->count(),
            ]);
        }

        return view('dashboard.index', [
            'role' => $role,
            'roleLabel' => ucfirst($role ?? 'Pengguna'),
        ]);
    }
}
