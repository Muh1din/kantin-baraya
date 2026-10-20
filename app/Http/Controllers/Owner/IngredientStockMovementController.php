<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\IngredientStockMovement;
use App\Services\IngredientStockService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use LogicException;

class IngredientStockMovementController extends Controller
{
    public function stockIn(Request $request, IngredientStockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'stock_in_ingredient_id' => ['required', 'integer', Rule::exists('ingredients', 'id')->where('is_active', true)],
            'stock_in_quantity' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:999999999999999.999'],
            'stock_in_notes' => ['nullable', 'string', 'max:5000'],
            'stock_in_token' => ['required', 'string', 'size:64'],
        ]);

        $transactionKey = $this->transactionKeyFor($request, $validated['stock_in_token'], IngredientStockMovement::TYPE_STOCK_IN);
        if (! $transactionKey) {
            return back()->withErrors(['stock_in_token' => 'Form stok masuk sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.'])->withInput();
        }

        try {
            $movement = $stockService->recordMovement(
                ingredientId: (int) $validated['stock_in_ingredient_id'],
                movementType: IngredientStockMovement::TYPE_STOCK_IN,
                quantityChange: $validated['stock_in_quantity'],
                transactionKey: $transactionKey,
                userId: $request->user()->id,
                notes: $validated['stock_in_notes'] ?? null,
            );
        } catch (DomainException|InvalidArgumentException|LogicException $exception) {
            return back()->withErrors(['stock_in_quantity' => $exception->getMessage()])->withInput();
        }

        return redirect()
            ->route('owner.ingredients.index')
            ->with('status', 'Stok masuk berhasil dicatat. Saldo sekarang ' . $movement->stock_after . '.');
    }

    public function adjustment(Request $request, IngredientStockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'adjustment_ingredient_id' => ['required', 'integer', Rule::exists('ingredients', 'id')],
            'adjustment_quantity' => ['required', 'numeric', 'decimal:0,3', 'not_in:0,-0,0.0,-0.0,0.00,-0.00,0.000,-0.000', 'max:999999999999999.999', 'min:-999999999999999.999'],
            'adjustment_reason' => ['required', 'string', 'max:5000'],
            'adjustment_token' => ['required', 'string', 'size:64'],
        ]);

        $transactionKey = $this->transactionKeyFor($request, $validated['adjustment_token'], IngredientStockMovement::TYPE_ADJUSTMENT);
        if (! $transactionKey) {
            return back()->withErrors(['adjustment_token' => 'Form penyesuaian sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.'])->withInput();
        }

        try {
            $movement = $stockService->recordMovement(
                ingredientId: (int) $validated['adjustment_ingredient_id'],
                movementType: IngredientStockMovement::TYPE_ADJUSTMENT,
                quantityChange: $validated['adjustment_quantity'],
                transactionKey: $transactionKey,
                userId: $request->user()->id,
                notes: $validated['adjustment_reason'],
            );
        } catch (DomainException|InvalidArgumentException|LogicException $exception) {
            return back()->withErrors(['adjustment_quantity' => $exception->getMessage()])->withInput();
        }

        return redirect()
            ->route('owner.ingredients.index')
            ->with('status', 'Penyesuaian stok berhasil dicatat. Saldo sekarang ' . $movement->stock_after . '.');
    }

    /** Resolve the opaque form nonce to a transaction key generated and held server-side. */
    private function transactionKeyFor(Request $request, string $token, string $movementType): ?string
    {
        $tokens = $request->session()->get('owner.stock_movement_tokens', []);
        $entry = $tokens[$token] ?? null;

        if (! is_array($entry) || ($entry['movement_type'] ?? null) !== $movementType) {
            return null;
        }

        if (($entry['created_at'] ?? 0) < now()->subDay()->timestamp) {
            unset($tokens[$token]);
            $request->session()->put('owner.stock_movement_tokens', $tokens);

            return null;
        }

        return $entry['transaction_key'] ?? null;
    }

    public static function issueFormToken(Request $request, string $movementType): string
    {
        $tokens = $request->session()->get('owner.stock_movement_tokens', []);
        $cutoff = now()->subDay()->timestamp;
        $tokens = array_filter($tokens, fn ($entry) => is_array($entry) && ($entry['created_at'] ?? 0) >= $cutoff);

        $token = Str::random(64);
        $tokens[$token] = [
            'movement_type' => $movementType,
            'transaction_key' => 'ingredient:' . $movementType . ':' . Str::uuid(),
            'created_at' => now()->timestamp,
        ];

        $request->session()->put('owner.stock_movement_tokens', $tokens);

        return $token;
    }
}
