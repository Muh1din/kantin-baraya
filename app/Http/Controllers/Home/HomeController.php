<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Table;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index()
    {
        $menus = Menu::query()
            ->where('is_available', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('home.index', compact('menus'));
    }

    public function tableMenu(string $token): View
    {
        $table = Table::query()
            ->where('qr_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $menus = Menu::query()
            ->where('is_available', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('home.table-menu', compact('table', 'menus'));
    }
}
