<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PelangganDashboardController extends Controller
{
    public function index(): View
    {
        return view('pelanggan.dashboard');
    }
}
