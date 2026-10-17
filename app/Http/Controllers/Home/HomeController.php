<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $menus = DB::table('menus')
            ->where('is_available', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('pelanggan.index', compact('menus'));
    }
}
