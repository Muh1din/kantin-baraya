<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Menu;

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
}
