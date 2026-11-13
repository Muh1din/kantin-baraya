<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    private const EMPLOYEE_ROLES = ['cashier', 'kasir', 'chef', 'koki'];

    public function index(): View
    {
        $users = User::query()
            ->with('role')
            ->whereHas('role', fn($query) => $query->whereIn('name', self::EMPLOYEE_ROLES))
            ->orderBy('name')
            ->paginate(10);

        return view('owner.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(['cashier', 'chef'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $role = Role::query()->firstOrCreate(['name' => $validated['role']]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $role->id,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('owner.users.index')
            ->with('status', 'Akun karyawan berhasil ditambahkan.');
    }

    public function destroy(int $user): RedirectResponse
    {
        $employee = User::query()
            ->whereKey($user)
            ->whereHas('role', fn($query) => $query->whereIn('name', self::EMPLOYEE_ROLES))
            ->firstOrFail();

        $employee->delete();

        return redirect()
            ->route('owner.users.index')
            ->with('status', 'Akun karyawan berhasil dihapus.');
    }
}
