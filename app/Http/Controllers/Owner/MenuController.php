<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        return view('owner.menus.index', [
            'menus' => Menu::query()->with('menuCategory')->orderBy('name')->paginate(10),
            'categories' => MenuCategory::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $category = MenuCategory::findOrFail($validated['category_id']);
        $validated['category'] = $category->name;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menus', 'public');
        }

        Menu::create($validated);

        return redirect()
            ->route('owner.menus.index')
            ->with('status', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu): View
    {
        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->when($menu->category_id, fn ($query) => $query->orWhere('id', $menu->category_id))
            ->orderBy('name')
            ->get();

        return view('owner.menus.edit', compact('menu', 'categories'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate($this->rules(requireActiveCategory: false));
        $category = MenuCategory::findOrFail($validated['category_id']);

        if (! $category->is_active && $category->id !== $menu->category_id) {
            return back()
                ->withErrors(['category_id' => 'Pilih kategori yang masih aktif.'])
                ->withInput();
        }

        $validated['category'] = $category->name;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menus', 'public');
        } else {
            unset($validated['image']);
        }

        $menu->update($validated);

        return redirect()
            ->route('owner.menus.index')
            ->with('status', 'Menu berhasil diperbarui.');
    }

    public function toggleStatus(Menu $menu): RedirectResponse
    {
        $menu->update(['is_available' => ! $menu->is_available]);

        $status = $menu->is_available ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('owner.menus.index')
            ->with('status', "Menu berhasil {$status}.");
    }

    private function rules(bool $requireActiveCategory = true): array
    {
        $categoryExists = Rule::exists('menu_categories', 'id');

        if ($requireActiveCategory) {
            $categoryExists->where('is_active', true);
        }

        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', $categoryExists],
            'price' => ['required', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
