<?php

namespace App\Http\Controllers\Cashier;

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

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:150']]);

        return view('cashier.inventory.index', [
            'ingredients' => Ingredient::query()->with('unit')
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->paginate(15)->withQueryString(),
            'filters' => $filters,
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function stockInForm(Request $request): View
    {
        return view('cashier.inventory.stock-in', [
            'ingredients' => Ingredient::query()->with('unit')->where('is_active', true)->orderBy('name')->get(),
            'stockInToken' => $this->issueMovementToken($request, IngredientStockMovement::TYPE_STOCK_IN),
        ]);
    }

    public function stockOutForm(Request $request): View
    {
        return view('cashier.inventory.stock-out', [
            'ingredients' => Ingredient::query()->with('unit')->orderBy('name')->get(),
            'stockOutToken' => $this->issueMovementToken($request, IngredientStockMovement::TYPE_STOCK_OUT),
        ]);
    }

    public function history(): View
    {
        return view('cashier.inventory.history', [
            'movements' => IngredientStockMovement::query()
                ->with(['ingredient.unit', 'user'])
                ->latest('occurred_at')
                ->paginate(25),
        ]);
    }

    public function store(Request $request, IngredientStockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('ingredients', 'name')],
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')],
            'opening_stock' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999999999.999'],
            'minimum_stock' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999999999.999'],
            'average_cost_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999999.99'],
            'opening_reason' => ['required', 'string', 'max:5000'],
        ]);

        $openingStock = $validated['opening_stock'];
        $reason = $validated['opening_reason'];
        unset($validated['opening_stock'], $validated['opening_reason']);
        $validated['is_active'] = true;

        $stockService->createWithOpeningBalance(
            attributes: $validated,
            openingQuantity: $openingStock,
            transactionKey: 'ingredient:kasir:opening:' . Str::uuid(),
            userId: $request->user()->id,
            notes: $reason,
        );

        return redirect()->route('kasir.inventory.index')->with('status', 'Bahan baku dan saldo awal berhasil dicatat.');
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('cashier.inventory.edit', [
            'ingredient' => $ingredient->load('unit'),
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('ingredients', 'name')->ignore($ingredient->id)],
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')],
            'minimum_stock' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:999999999999999.999'],
            'average_cost_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999999.99'],
        ]);

        $ingredient->update($validated);

        return redirect()->route('kasir.inventory.index')->with('status', 'Informasi bahan baku berhasil diperbarui.');
    }

    public function stockIn(Request $request, IngredientStockService $stockService): RedirectResponse
    {
        return $this->recordMovement($request, IngredientStockMovement::TYPE_STOCK_IN, $stockService);
    }

    public function stockOut(Request $request, IngredientStockService $stockService): RedirectResponse
    {
        return $this->recordMovement($request, IngredientStockMovement::TYPE_STOCK_OUT, $stockService);
    }

    private function recordMovement(Request $request, string $type, IngredientStockService $stockService): RedirectResponse
    {
        $prefix = $type === IngredientStockMovement::TYPE_STOCK_IN ? 'stock_in' : 'stock_out';
        $ingredientRule = Rule::exists('ingredients', 'id');
        if ($type === IngredientStockMovement::TYPE_STOCK_IN) {
            $ingredientRule->where('is_active', true);
        }

        $validated = $request->validate([
            "{$prefix}_ingredient_id" => ['required', 'integer', $ingredientRule],
            "{$prefix}_quantity" => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:999999999999999.999'],
            "{$prefix}_reason" => ['required', 'string', 'max:5000'],
            "{$prefix}_token" => ['required', 'string', 'size:64'],
        ]);

        $transactionKey = $this->resolveMovementToken($request, $validated["{$prefix}_token"], $type);
        if (! $transactionKey) {
            return back()->withErrors(["{$prefix}_token" => 'Form pergerakan stok kedaluwarsa. Muat ulang halaman lalu coba lagi.'])->withInput();
        }

        $quantity = $validated["{$prefix}_quantity"];
        if ($type === IngredientStockMovement::TYPE_STOCK_OUT) {
            $quantity = '-' . ltrim($quantity, '+');
        }

        try {
            $movement = $stockService->recordMovement(
                ingredientId: (int) $validated["{$prefix}_ingredient_id"],
                movementType: $type,
                quantityChange: $quantity,
                transactionKey: $transactionKey,
                userId: $request->user()->id,
                notes: $validated["{$prefix}_reason"],
            );
        } catch (\DomainException|\InvalidArgumentException|\LogicException $exception) {
            return back()->withErrors(["{$prefix}_quantity" => $exception->getMessage()])->withInput();
        }

        $label = $type === IngredientStockMovement::TYPE_STOCK_IN ? 'Stok masuk' : 'Stok keluar';

        $destination = $type === IngredientStockMovement::TYPE_STOCK_IN
            ? 'kasir.inventory.stock-in.form'
            : 'kasir.inventory.stock-out.form';

        return redirect()->route($destination)->with('status', "{$label} berhasil dicatat. Saldo sekarang {$movement->stock_after}.");
    }

    private function issueMovementToken(Request $request, string $movementType): string
    {
        $tokens = $request->session()->get('kasir.stock_movement_tokens', []);
        $cutoff = now()->subDay()->timestamp;
        $tokens = array_filter($tokens, fn ($entry) => is_array($entry) && ($entry['created_at'] ?? 0) >= $cutoff);

        $token = Str::random(64);
        $tokens[$token] = [
            'movement_type' => $movementType,
            'transaction_key' => 'ingredient:kasir:' . $movementType . ':' . Str::uuid(),
            'created_at' => now()->timestamp,
        ];
        $request->session()->put('kasir.stock_movement_tokens', $tokens);

        return $token;
    }

    private function resolveMovementToken(Request $request, string $token, string $movementType): ?string
    {
        $entry = $request->session()->get("kasir.stock_movement_tokens.{$token}");

        if (! is_array($entry)
            || ($entry['movement_type'] ?? null) !== $movementType
            || ($entry['created_at'] ?? 0) < now()->subDay()->timestamp) {
            return null;
        }

        return $entry['transaction_key'] ?? null;
    }
}
