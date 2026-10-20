<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuCategoryController extends Controller
{
    public function index(): View
    {
        return view('owner.menu-categories.index', [
            'categories' => MenuCategory::query()->orderBy('name')->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('menu_categories', 'name')],
            'description' => ['nullable', 'string'],
        ]);

        MenuCategory::create($validated);

        return redirect()
            ->route('owner.menu-categories.index')
            ->with('status', 'Kategori menu berhasil ditambahkan.');
    }

    public function edit(MenuCategory $menuCategory): View
    {
        return view('owner.menu-categories.edit', compact('menuCategory'));
    }

    public function update(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menu_categories', 'name')->ignore($menuCategory->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $menuCategory->update($validated);

        return redirect()
            ->route('owner.menu-categories.index')
            ->with('status', 'Kategori menu berhasil diperbarui.');
    }

    public function toggleStatus(MenuCategory $menuCategory): RedirectResponse
    {
        $menuCategory->update(['is_active' => ! $menuCategory->is_active]);

        $status = $menuCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('owner.menu-categories.index')
            ->with('status', "Kategori menu berhasil {$status}.");
    }
}
