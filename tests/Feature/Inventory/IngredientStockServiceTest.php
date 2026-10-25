<?php

namespace Tests\Feature\Inventory;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Unit;
use App\Services\IngredientStockService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class IngredientStockServiceTest extends TestCase
{
    use RefreshDatabase;

    private int $unitSequence = 0;

    private function unit(): Unit
    {
        $this->unitSequence++;

        return Unit::create([
            'name' => 'Unit ' . $this->unitSequence,
            'abbreviation' => 'u' . $this->unitSequence,
        ]);
    }

    private function ingredient(array $overrides = []): Ingredient
    {
        $attributes = array_merge([
            'unit_id' => $this->unit()->id,
            'name' => 'Tepung Terigu',
            'minimum_stock' => '1.000',
            'average_cost_price' => '12000.00',
            'is_active' => true,
        ], $overrides);
        $currentStock = $attributes['current_stock'] ?? '0.000';
        unset($attributes['current_stock']);

        $ingredient = Ingredient::create($attributes);

        if ($currentStock !== '0.000') {
            $ingredient->forceFill(['current_stock' => $currentStock])->save();
        }

        return $ingredient;
    }

    public function test_opening_balance_and_movement_are_created_together(): void
    {
        $ingredient = app(IngredientStockService::class)->createWithOpeningBalance([
            'unit_id' => $this->unit()->id,
            'name' => 'Gula Pasir',
            'minimum_stock' => '2.000',
            'average_cost_price' => '16000.00',
            'is_active' => true,
        ], '5.250', 'ingredient:gula:opening:v1');

        $movement = $ingredient->stockMovements()->sole();

        $this->assertSame('5.250', $ingredient->current_stock);
        $this->assertSame(IngredientStockMovement::TYPE_OPENING_BALANCE, $movement->movement_type);
        $this->assertSame('5.250', $movement->quantity_change);
        $this->assertSame('5.250', $movement->stock_after);
    }

    public function test_decimal_movements_are_exact_and_transaction_key_is_idempotent(): void
    {
        $ingredient = $this->ingredient(['name' => 'Gula']);
        $service = app(IngredientStockService::class);

        $service->recordOpeningBalance($ingredient->id, '0.100', 'gula:opening');
        $first = $service->recordMovement(
            $ingredient->id,
            IngredientStockMovement::TYPE_STOCK_IN,
            '0.200',
            'gula:receipt:123',
        );
        $retry = $service->recordMovement(
            $ingredient->id,
            IngredientStockMovement::TYPE_STOCK_IN,
            '0.200',
            'gula:receipt:123',
        );

        $this->assertSame($first->id, $retry->id);
        $this->assertSame('0.300', $ingredient->refresh()->current_stock);
        $this->assertSame(2, $ingredient->stockMovements()->count());
    }

    public function test_reusing_transaction_key_for_a_different_movement_is_rejected(): void
    {
        $ingredient = $this->ingredient(['name' => 'Minyak']);
        $service = app(IngredientStockService::class);
        $service->recordOpeningBalance($ingredient->id, '4.000', 'minyak:opening');
        $service->recordMovement($ingredient->id, IngredientStockMovement::TYPE_STOCK_IN, '1.000', 'minyak:receipt');

        try {
            $service->recordMovement($ingredient->id, IngredientStockMovement::TYPE_STOCK_IN, '2.000', 'minyak:receipt');
            $this->fail('Transaction key yang sama dengan payload berbeda harus ditolak.');
        } catch (LogicException) {
            $this->assertSame('5.000', $ingredient->refresh()->current_stock);
            $this->assertSame(2, $ingredient->stockMovements()->count());
        }
    }

    public function test_stock_cannot_become_negative(): void
    {
        $ingredient = $this->ingredient(['name' => 'Garam']);
        $service = app(IngredientStockService::class);
        $service->recordOpeningBalance($ingredient->id, '2.000', 'garam:opening');

        try {
            $service->recordMovement($ingredient->id, IngredientStockMovement::TYPE_STOCK_OUT, '-2.001', 'garam:out:1');
            $this->fail('Saldo negatif harus ditolak.');
        } catch (DomainException) {
            $this->assertSame('2.000', $ingredient->refresh()->current_stock);
            $this->assertSame(1, $ingredient->stockMovements()->count());
        }
    }

    public function test_balance_update_rolls_back_if_movement_insert_fails(): void
    {
        $ingredient = $this->ingredient(['name' => 'Susu']);

        try {
            app(IngredientStockService::class)->recordMovement(
                $ingredient->id,
                IngredientStockMovement::TYPE_STOCK_IN,
                '3.000',
                'susu:receipt:invalid-user',
                userId: 999999,
            );
            $this->fail('Foreign key user yang tidak valid harus menggagalkan pencatatan movement.');
        } catch (QueryException) {
            $this->assertSame('0.000', $ingredient->refresh()->current_stock);
            $this->assertSame(0, $ingredient->stockMovements()->count());
        }
    }

    public function test_low_stock_scope_includes_stock_equal_to_minimum(): void
    {
        $equal = $this->ingredient(['name' => 'Beras Pas', 'current_stock' => '2.000', 'minimum_stock' => '2.000']);
        $below = $this->ingredient(['name' => 'Beras Rendah', 'current_stock' => '1.999', 'minimum_stock' => '2.000']);
        $this->ingredient(['name' => 'Beras Aman', 'current_stock' => '2.001', 'minimum_stock' => '2.000']);

        $lowStockIds = Ingredient::query()->lowStock()->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$equal->id, $below->id], $lowStockIds);
    }

    public function test_unit_and_ingredient_cannot_be_deleted_while_referenced(): void
    {
        $unit = $this->unit();
        $ingredient = $this->ingredient(['unit_id' => $unit->id]);
        app(IngredientStockService::class)->recordOpeningBalance($ingredient->id, '1.000', 'flour:opening');

        try {
            $ingredient->delete();
            $this->fail('Bahan dengan riwayat stok tidak boleh dihapus.');
        } catch (QueryException) {
            $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id]);
        }

        try {
            $unit->delete();
            $this->fail('Satuan yang masih digunakan tidak boleh dihapus.');
        } catch (QueryException) {
            $this->assertDatabaseHas('units', ['id' => $unit->id]);
        }
    }
}
