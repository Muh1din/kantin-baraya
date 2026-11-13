<?php

namespace Tests\Feature\Cashier;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class InventoryTest extends TestCase
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
        return Unit::query()->create([
            'name' => 'Satuan ' . ++$this->unitSequence,
            'abbreviation' => 'u' . $this->unitSequence,
        ]);
    }

    private function ingredient(Unit $unit, string $name, string $stock, string $minimum, bool $active = true): Ingredient
    {
        $ingredient = Ingredient::query()->create([
            'unit_id' => $unit->id,
            'name' => $name,
            'minimum_stock' => $minimum,
            'average_cost_price' => 1000,
            'is_active' => $active,
        ]);
        $ingredient->forceFill(['current_stock' => $stock])->save();

        return $ingredient;
    }

    public function test_cashier_can_see_current_ingredient_stocks_units_and_statuses(): void
    {
        $cashier = $this->userWithRole('kasir');
        $unit = $this->unit();
        $low = $this->ingredient($unit, 'Tepung Terigu', '1.250', '2.000');
        $this->ingredient($unit, 'Gula Pasir', '5.000', '2.000', false);

        $this->actingAs($cashier)
            ->get(route('kasir.inventory.index'))
            ->assertOk()
            ->assertSee('Tepung Terigu')
            ->assertSee('Gula Pasir')
            ->assertSee('1.250')
            ->assertSee('5.000')
            ->assertSee('Stok rendah')
            ->assertSee('Stok aman')
            ->assertSee('Nonaktif')
            ->assertSee('Stok Masuk')
            ->assertSee('Stok Keluar')
            ->assertSee('Riwayat Stok')
            ->assertDontSee('stock_in_quantity')
            ->assertDontSee('stock_out_quantity')
            ->assertDontSee('Riwayat Perubahan Stok');

        $low->forceFill(['current_stock' => '7.125'])->save();
        $this->get(route('kasir.inventory.index'))
            ->assertOk()
            ->assertSee('7.125')
            ->assertDontSee('Stok rendah');
    }

    public function test_cashier_can_search_inventory_by_ingredient_name(): void
    {
        $cashier = $this->userWithRole('kasir');
        $unit = $this->unit();
        $this->ingredient($unit, 'Tepung Terigu', '3.000', '1.000');
        $this->ingredient($unit, 'Gula Pasir', '4.000', '1.000');

        $this->actingAs($cashier)
            ->get(route('kasir.inventory.index', ['search' => 'tepung']))
            ->assertOk()
            ->assertSee('Tepung Terigu')
            ->assertViewHas('ingredients', fn ($ingredients) => $ingredients->total() === 1
                && $ingredients->first()->name === 'Tepung Terigu');
    }

    public function test_cashier_can_create_and_edit_ingredient_without_directly_editing_stock(): void
    {
        $cashier = $this->userWithRole('kasir');
        $unit = $this->unit();

        $this->actingAs($cashier)
            ->post(route('kasir.inventory.store'), [
                'name' => 'Minyak Goreng',
                'unit_id' => $unit->id,
                'opening_stock' => '8.500',
                'minimum_stock' => '2.000',
                'average_cost_price' => '18000.00',
                'opening_reason' => 'Saldo awal saat bahan dicatat',
            ])
            ->assertRedirect(route('kasir.inventory.index'));

        $ingredient = Ingredient::query()->where('name', 'Minyak Goreng')->sole();
        $openingMovement = $ingredient->stockMovements()->sole();
        $this->assertSame('8.500', $ingredient->current_stock);
        $this->assertSame(IngredientStockMovement::TYPE_OPENING_BALANCE, $openingMovement->movement_type);
        $this->assertSame($cashier->id, $openingMovement->user_id);
        $this->assertSame('Saldo awal saat bahan dicatat', $openingMovement->notes);

        $this->put(route('kasir.inventory.update', $ingredient), [
            'name' => 'Minyak Sawit',
            'unit_id' => $unit->id,
            'minimum_stock' => '3.000',
            'average_cost_price' => '19000.00',
            'current_stock' => '0',
        ])->assertRedirect(route('kasir.inventory.index'));

        $ingredient->refresh();
        $this->assertSame('8.500', $ingredient->current_stock);
        $this->assertSame('3.000', $ingredient->minimum_stock);
        $this->assertSame(1, $ingredient->stockMovements()->count());
        $this->assertDatabaseMissing('ingredients', ['name' => 'Minyak Goreng']);
    }

    public function test_stock_in_and_out_are_atomic_audited_and_require_a_reason(): void
    {
        $cashier = $this->userWithRole('kasir');
        $unit = $this->unit();
        $ingredient = $this->ingredient($unit, 'Beras', '10.000', '2.000');

        $this->actingAs($cashier)->get(route('kasir.inventory.stock-in.form'))->assertOk();
        $session = session('kasir.stock_movement_tokens', []);
        $stockInToken = collect($session)->firstWhere('movement_type', IngredientStockMovement::TYPE_STOCK_IN);
        $stockInTokenValue = collect($session)->search($stockInToken);

        $this->post(route('kasir.inventory.stock-in'), [
            'stock_in_token' => $stockInTokenValue,
            'stock_in_ingredient_id' => $ingredient->id,
            'stock_in_quantity' => '2.500',
            'stock_in_reason' => 'Pembelian supplier',
        ])->assertRedirect(route('kasir.inventory.stock-in.form'));

        $this->assertSame('12.500', $ingredient->fresh()->current_stock);
        $this->get(route('kasir.inventory.stock-out.form'))->assertOk();
        $session = session('kasir.stock_movement_tokens', []);
        $stockOutToken = collect($session)->firstWhere('movement_type', IngredientStockMovement::TYPE_STOCK_OUT);
        $stockOutTokenValue = collect($session)->search($stockOutToken);
        $this->post(route('kasir.inventory.stock-out'), [
            'stock_out_token' => $stockOutTokenValue,
            'stock_out_ingredient_id' => $ingredient->id,
            'stock_out_quantity' => '3.250',
            'stock_out_reason' => 'Bahan rusak',
        ])->assertRedirect(route('kasir.inventory.stock-out.form'));

        $this->assertSame('9.250', $ingredient->fresh()->current_stock);
        $this->assertDatabaseHas('ingredient_stock_movements', [
            'ingredient_id' => $ingredient->id,
            'user_id' => $cashier->id,
            'movement_type' => IngredientStockMovement::TYPE_STOCK_IN,
            'quantity_change' => '2.500',
            'notes' => 'Pembelian supplier',
        ]);
        $this->assertDatabaseHas('ingredient_stock_movements', [
            'ingredient_id' => $ingredient->id,
            'user_id' => $cashier->id,
            'movement_type' => IngredientStockMovement::TYPE_STOCK_OUT,
            'quantity_change' => '-3.250',
            'notes' => 'Bahan rusak',
            'stock_after' => '9.250',
        ]);

        $this->get(route('kasir.inventory.history'))
            ->assertOk()
            ->assertSee('Pembelian supplier')
            ->assertSee('Bahan rusak')
            ->assertSee('-3.250');
    }

    public function test_stock_out_cannot_make_balance_negative_or_write_a_movement(): void
    {
        $cashier = $this->userWithRole('kasir');
        $unit = $this->unit();
        $ingredient = $this->ingredient($unit, 'Susu', '1.000', '0.500');

        $this->actingAs($cashier)->get(route('kasir.inventory.stock-out.form'));
        $session = session('kasir.stock_movement_tokens', []);
        $token = collect($session)->firstWhere('movement_type', IngredientStockMovement::TYPE_STOCK_OUT);
        $tokenValue = collect($session)->search($token);

        $this->from(route('kasir.inventory.stock-out.form'))
            ->post(route('kasir.inventory.stock-out'), [
                'stock_out_token' => $tokenValue,
                'stock_out_ingredient_id' => $ingredient->id,
                'stock_out_quantity' => '1.001',
                'stock_out_reason' => 'Pemakaian harian',
            ])
            ->assertSessionHasErrors('stock_out_quantity');

        $this->assertSame('1.000', $ingredient->fresh()->current_stock);
        $this->assertDatabaseCount('ingredient_stock_movements', 0);
    }

    public function test_inventory_responsibilities_have_separate_pages_and_forms(): void
    {
        $cashier = $this->userWithRole('kasir');
        $unit = $this->unit();
        $this->ingredient($unit, 'Beras', '4.000', '1.000');

        $this->actingAs($cashier)
            ->get(route('kasir.inventory.stock-in.form'))
            ->assertOk()->assertSee('Stok Masuk')->assertSee('stock_in_quantity')->assertDontSee('stock_out_quantity');

        $this->get(route('kasir.inventory.stock-out.form'))
            ->assertOk()->assertSee('Stok Keluar')->assertSee('stock_out_quantity')->assertDontSee('stock_in_quantity');

        $this->get(route('kasir.inventory.history'))
            ->assertOk()->assertSee('Riwayat Stok')->assertSee('Pergerakan Stok');
    }

    public function test_inventory_routes_are_cashier_only_and_have_no_delete_endpoint(): void
    {
        $customer = $this->userWithRole('customer');
        $this->actingAs($customer)->get(route('kasir.inventory.index'))->assertForbidden();
        $this->post('/kasir/inventory')->assertForbidden();

        $cashier = $this->userWithRole('kasir');
        $this->actingAs($cashier)->delete('/kasir/inventory/1')->assertMethodNotAllowed();

        Auth::logout();
        $this->get(route('kasir.inventory.index'))->assertRedirect(route('login'));
    }
}
