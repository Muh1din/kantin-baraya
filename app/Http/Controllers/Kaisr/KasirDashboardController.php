<?php

namespace App\Http\Controllers\Kaisr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KasirDashboardController extends Controller
{
    public function index()
    {
        return view('kasir.dashboard');
    }
}
