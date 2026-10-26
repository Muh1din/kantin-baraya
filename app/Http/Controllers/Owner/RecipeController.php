<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuIngredient;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecipeController extends Controller
{
    public function index(): View
    {
        return view('owner.recipes.index', [
            'menus' => Menu::query()
                ->with('menuCategory')
                ->withCount('recipeIngredients')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function show(Menu $menu): View
    {
        $recipeIngredients = $menu->recipeIngredients()->with(['ingredient.unit'])->orderBy('id')->get();

        return view('owner.recipes.show', [
            'menu' => $menu,
            'recipeIngredients' => $recipeIngredients,
            'activeIngredients' => Ingredient::query()
                ->where('is_active', true)
                ->whereDoesntHave('menuRecipes', fn ($query) => $query->where('menu_id', $menu->id))
                ->with('unit')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeIngredients(Request $request, Menu $menu): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.ingredient_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('ingredients', 'id')->where('is_active', true),
            ],
            'ingredients.*.quantity' => [
                'required',
                'numeric',
                'decimal:0,3',
                'gt:0',
                'max:999999999999999.999',
            ],
        ]);

        $validator->validate();
        $rows = $validator->validated()['ingredients'];
        $ingredientIds = array_map('intval', array_column($rows, 'ingredient_id'));

        $existingIds = $menu->recipeIngredients()
            ->whereIn('ingredient_id', $ingredientIds)
            ->pluck('ingredient_id')
            ->all();

        if ($existingIds !== []) {
            return back()
                ->withErrors(['ingredients' => 'Bahan yang sama sudah ada di komposisi menu ini.'])
                ->withInput();
        }

        try {
            DB::transaction(function () use ($menu, $rows): void {
                foreach ($rows as $row) {
                    $menu->recipeIngredients()->create([
                        'ingredient_id' => $row['ingredient_id'],
                        'quantity' => $row['quantity'],
                    ]);
                }
            });
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withErrors(['ingredients' => 'Salah satu bahan sudah ditambahkan ke resep ini. Muat ulang halaman lalu coba lagi.'])
                ->withInput();
        }

        return redirect()
            ->route('owner.recipes.show', $menu)
            ->with('status', 'Komposisi bahan berhasil ditambahkan.');
    }

    public function updateQuantity(Request $request, Menu $menu, MenuIngredient $menuIngredient): RedirectResponse
    {
        abort_unless($menuIngredient->menu_id === $menu->id, 404);

        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:999999999999999.999'],
        ]);

        $menuIngredient->update(['quantity' => $validated['quantity']]);

        return redirect()
            ->route('owner.recipes.show', $menu)
            ->with('status', 'Jumlah bahan per porsi berhasil diperbarui.');
    }

    public function destroyIngredient(Menu $menu, MenuIngredient $menuIngredient): RedirectResponse
    {
        abort_unless($menuIngredient->menu_id === $menu->id, 404);
        $menuIngredient->delete();

        return redirect()
            ->route('owner.recipes.show', $menu)
            ->with('status', 'Bahan berhasil dihapus dari komposisi menu.');
    }
}
