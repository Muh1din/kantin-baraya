<?php

namespace Tests\Feature\Owner;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\IngredientStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class IngredientManagementTest extends TestCase
{
    use RefreshDatabase;

    private int $unitSequence = 0;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function unit(): Unit
    {
        $this->unitSequence++;

        return Unit::create([
            'name' => 'Satuan ' . $this->unitSequence,
            'abbreviation' => 's' . $this->unitSequence,
        ]);
    }

    private function ingredientData(Unit $unit, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tepung Terigu',
            'unit_id' => $unit->id,
            'opening_stock' => '5.250',
            'minimum_stock' => '2.000',
            'average_cost_price' => '12000.50',
        ], $overrides);
    }

    private function createIngredient(Unit $unit, array $overrides = []): Ingredient
    {
        $attributes = array_merge([
            'name' => 'Tepung Terigu',
            'unit_id' => $unit->id,
            'minimum_stock' => '2.000',
            'average_cost_price' => '12000.50',
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

    public function test_owner_can_view_ingredients_and_low_stock_indicator(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $unit = $this->unit();
        $this->createIngredient($unit, ['name' => 'Stok Pas', 'current_stock' => '2.000']);
        $this->createIngredient($unit, ['name' => 'Stok Aman', 'current_stock' => '3.000']);

        $this->get(route('owner.ingredients.index'))
            ->assertOk()
            ->assertSee('Stok Pas')
            ->assertSee('Stok Aman')
            ->assertSee('Stok rendah')
            ->assertSee($unit->abbreviation)
            ->assertSee('Tambah bahan baku');
    }

    public function test_owner_can_add_ingredient_and_opening_stock_is_recorded_once(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $unit = $this->unit();

        $this->post(route('owner.ingredients.store'), $this->ingredientData($unit))
            ->assertRedirect(route('owner.ingredients.index'))
            ->assertSessionHas('status');

        $ingredient = Ingredient::query()->where('name', 'Tepung Terigu')->sole();
        $movement = $ingredient->stockMovements()->sole();

        $this->assertSame('5.250', $ingredient->current_stock);
        $this->assertSame(IngredientStockMovement::TYPE_OPENING_BALANCE, $movement->movement_type);
        $this->assertSame('5.250', $movement->quantity_change);
        $this->assertSame('5.250', $movement->stock_after);
        $this->assertSame(auth()->id(), $movement->user_id);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id, 'current_stock' => '5.250']);
    }

    public function test_owner_can_add_zero_opening_balance_and_it_is_still_audited(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $unit = $this->unit();

        $this->post(route('owner.ingredients.store'), $this->ingredientData($unit, ['opening_stock' => '0']))
            ->assertRedirect(route('owner.ingredients.index'));

        $ingredient = Ingredient::query()->where('name', 'Tepung Terigu')->sole();
        $this->assertSame('0.000', $ingredient->current_stock);
        $this->assertSame('0.000', $ingredient->stockMovements()->sole()->quantity_change);
    }

    public function test_failed_opening_movement_rolls_back_ingredient_creation(): void
    {
        $unit = $this->unit();

        try {
            app(IngredientStockService::class)->createWithOpeningBalance([
                'unit_id' => $unit->id,
                'name' => 'Bahan Gagal',
                'minimum_stock' => '1.000',
                'average_cost_price' => '100.00',
                'is_active' => true,
            ], 'invalid-quantity', 'ingredient:failed-opening');
            $this->fail('Kuantitas opening yang tidak valid harus menggagalkan pembuatan bahan.');
        } catch (InvalidArgumentException) {
            $this->assertDatabaseMissing('ingredients', ['name' => 'Bahan Gagal']);
            $this->assertDatabaseMissing('ingredient_stock_movements', ['transaction_key' => 'ingredient:failed-opening']);
        }
    }

    public function test_owner_can_edit_information_without_changing_current_stock_or_history(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $unit = $this->unit();
        $ingredient = $this->createIngredient($unit, ['current_stock' => '7.125']);
        $movement = $ingredient->stockMovements()->create([
            'user_id' => auth()->id(),
            'movement_type' => IngredientStockMovement::TYPE_OPENING_BALANCE,
            'quantity_change' => '7.125',
            'stock_after' => '7.125',
            'transaction_key' => 'edit-test-opening',
            'notes' => 'fixture',
            'occurred_at' => now(),
        ]);
        $newUnit = $this->unit();

        $this->put(route('owner.ingredients.update', $ingredient), [
            'name' => 'Tepung Berprotein Tinggi',
            'unit_id' => $newUnit->id,
            'minimum_stock' => '3.500',
            'average_cost_price' => '15000.25',
            'current_stock' => '0',
        ])->assertRedirect(route('owner.ingredients.index'));

        $ingredient->refresh();
        $this->assertSame('7.125', $ingredient->current_stock);
        $this->assertSame('3.500', $ingredient->minimum_stock);
        $this->assertSame($newUnit->id, $ingredient->unit_id);
        $this->assertSame(1, $ingredient->stockMovements()->count());
        $this->assertDatabaseHas('ingredient_stock_movements', ['id' => $movement->id]);
    }

    public function test_owner_can_toggle_active_status_without_deleting_stock_history(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $unit = $this->unit();
        $ingredient = $this->createIngredient($unit);
        $movement = $ingredient->stockMovements()->create([
            'movement_type' => IngredientStockMovement::TYPE_OPENING_BALANCE,
            'quantity_change' => '0.000',
            'stock_after' => '0.000',
            'transaction_key' => 'status-test-opening',
            'occurred_at' => now(),
        ]);

        $this->patch(route('owner.ingredients.toggle-status', $ingredient))
            ->assertRedirect(route('owner.ingredients.index'));

        $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id, 'is_active' => false]);
        $this->assertDatabaseHas('ingredient_stock_movements', ['id' => $movement->id]);
    }

    public function test_invalid_fields_and_unknown_unit_are_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $unit = $this->unit();

        $this->from(route('owner.ingredients.index'))
            ->post(route('owner.ingredients.store'), $this->ingredientData($unit, [
                'name' => '',
                'unit_id' => 999999,
                'opening_stock' => '-1',
                'minimum_stock' => '1.2345',
                'average_cost_price' => '-0.01',
            ]))
            ->assertSessionHasErrors(['name', 'unit_id', 'opening_stock', 'minimum_stock', 'average_cost_price']);

        $this->assertDatabaseCount('ingredients', 0);
        $this->assertDatabaseCount('ingredient_stock_movements', 0);
    }

    public function test_duplicate_ingredient_name_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $unit = $this->unit();
        $this->createIngredient($unit);

        $this->from(route('owner.ingredients.index'))
            ->post(route('owner.ingredients.store'), $this->ingredientData($unit))
            ->assertSessionHasErrors('name');
    }

    public function test_owner_only_endpoints_are_forbidden_to_other_roles_and_require_authentication(): void
    {
        $unit = $this->unit();
        $ingredient = $this->createIngredient($unit);
        $kasir = $this->userWithRole('kasir');

        $this->actingAs($kasir)->get(route('owner.ingredients.index'))->assertForbidden();
        $this->actingAs($kasir)->post(route('owner.ingredients.store'), $this->ingredientData($unit))->assertForbidden();
        $this->actingAs($kasir)->get(route('owner.ingredients.edit', $ingredient))->assertForbidden();
        $this->actingAs($kasir)->put(route('owner.ingredients.update', $ingredient), [])->assertForbidden();
        $this->actingAs($kasir)->patch(route('owner.ingredients.toggle-status', $ingredient))->assertForbidden();

        auth()->logout();
        $this->get(route('owner.ingredients.index'))->assertRedirect(route('login'));
    }

    public function test_missing_ingredient_is_not_found(): void
    {
        $this->actingAs($this->userWithRole('owner'));

        $this->get(route('owner.ingredients.edit', 999999))->assertNotFound();
        $this->put(route('owner.ingredients.update', 999999), [])->assertNotFound();
        $this->patch(route('owner.ingredients.toggle-status', 999999))->assertNotFound();
    }
}
