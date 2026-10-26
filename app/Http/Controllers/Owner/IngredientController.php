<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Unit;
use App\Services\IngredientStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(): View
    {
        return view('owner.ingredients.index', [
            'ingredients' => Ingredient::query()->with('unit')->orderBy('name')->paginate(10),
            'allIngredients' => Ingredient::query()->with('unit')->orderBy('name')->get(),
            'activeIngredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(),
            'recentMovements' => IngredientStockMovement::query()->with(['ingredient.unit', 'user'])->latest('occurred_at')->limit(10)->get(),
            'units' => Unit::query()->orderBy('name')->get(),
            'stockInToken' => IngredientStockMovementController::issueFormToken(request(), IngredientStockMovement::TYPE_STOCK_IN),
            'adjustmentToken' => IngredientStockMovementController::issueFormToken(request(), IngredientStockMovement::TYPE_ADJUSTMENT),
        ]);
    }

    public function store(Request $request, IngredientStockService $stockService): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $openingStock = $validated['opening_stock'];
        unset($validated['opening_stock']);

        $stockService->createWithOpeningBalance(
            attributes: $validated,
            openingQuantity: $openingStock,
            transactionKey: 'ingredient:opening:' . Str::uuid(),
            userId: $request->user()->id,
            notes: 'Saldo awal saat bahan baku dibuat.',
        );

        return redirect()
            ->route('owner.ingredients.index')
            ->with('status', 'Bahan baku berhasil ditambahkan dan saldo awal dicatat.');
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('owner.ingredients.edit', [
            'ingredient' => $ingredient->load('unit'),
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $validated = $request->validate($this->rules($ingredient));
        $ingredient->update($validated);

        return redirect()
            ->route('owner.ingredients.index')
            ->with('status', 'Informasi bahan baku berhasil diperbarui.');
    }

    public function toggleStatus(Ingredient $ingredient): RedirectResponse
    {
        $ingredient->update(['is_active' => ! $ingredient->is_active]);
        $status = $ingredient->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('owner.ingredients.index')
            ->with('status', "Bahan baku berhasil {$status}.");
    }

    private function rules(?Ingredient $ingredient = null): array
    {
        $nameRule = Rule::unique('ingredients', 'name');
        if ($ingredient) {
            $nameRule->ignore($ingredient->id);
        }

        $rules = [
            'name' => ['required', 'string', 'max:150', $nameRule],
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')],
            'minimum_stock' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999999999.999'],
            'average_cost_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999999.99'],
        ];

        if (! $ingredient) {
            $rules['opening_stock'] = ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999999999.999'];
        }

        return $rules;
    }
}
