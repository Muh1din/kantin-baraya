<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Role;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $route = $this->dashboardRoute(Auth::user()->role?->name);

            if ($route) {
                return redirect()->route($route);
            }
        }

        return view('auth.login');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi input login
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Autentikasi pengguna
        if (! Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors([
                    'email' => 'Email atau password tidak sesuai.',
                ])
                ->onlyInput('email');
        }

        // 3. Regenerasi session setelah login
        $request->session()->regenerate();

        // 4. Ambil role pengguna
        $dashboardRoute = $this->dashboardRoute($request->user()->role?->name);

        // 6. Tolak akun yang tidak memiliki role yang diizinkan
        if ($dashboardRoute === null) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda tidak memiliki akses ke aplikasi.',
                ]);
        }

        // 7. Arahkan pengguna ke dashboard sesuai role
        return redirect()->intended(route($dashboardRoute));
    }

    private function dashboardRoute(?string $roleName): ?string
    {
        return match ($roleName) {
            'owner' => 'owner.dashboard',
            'kasir',
            'cashier' => 'kasir.dashboard',
            'pelanggan' => 'pelanggan.dashboard',
            'customer' => 'customer.dashboard',
            'koki',
            'chef' => 'koki.dashboard',
            default => null,
        };
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
