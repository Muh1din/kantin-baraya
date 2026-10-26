<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $menuQuery = Menu::query()
            ->with('menuCategory')
            ->where('is_available', true)
            ->where('stock', '>', 0);

        if ($request->filled('kategori') && $request->query('kategori') !== 'all') {
            $menuQuery->where('category_id', $request->query('kategori'));
        }

        if ($request->filled('search')) {
            $menuQuery->where('name', 'like', '%' . $request->query('search') . '%');
        }

        return view('customer.menus.index', [
            'categories' => $categories,
            'menus' => $menuQuery->orderBy('name')->get(),
            'selectedCategory' => $request->query('kategori', 'all'),
            'searchQuery' => $request->query('search', ''),
            'mode' => $request->query('mode', 'dine-in'),
        ]);
    }
}
