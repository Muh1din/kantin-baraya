<?php

namespace Tests\Feature\Cashier;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Unit;
use App\Models\User;
use App\Services\IngredientStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $role = Role::firstOrCreate(['name' => $role]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function menu(string $name = 'Nasi Goreng', int $price = 12000, bool $available = true): Menu
    {
        return Menu::create([
            'name' => $name,
            'category' => 'makanan',
            'price' => $price,
            'stock' => 7,
            'is_available' => $available,
        ]);
    }

    private function checkoutToken(): string
    {
        $this->get(route('kasir.pos.index'))->assertOk();
        $tokens = session('kasir.pos.checkout_tokens');

        return (string) array_key_last($tokens);
    }

    public function test_cashier_can_access_pos_and_only_available_menu_is_shown(): void
    {
        $cashier = $this->userWithRole('kasir');
        $available = $this->menu();
        $unavailable = $this->menu('Menu Tutup', 8000, false);

        $this->actingAs($cashier)->get(route('kasir.pos.index'))
            ->assertOk()->assertSee('POS Kasir')->assertSee($available->name)->assertSee('12.000')->assertDontSee($unavailable->name);
    }

    public function test_guest_and_non_cashier_cannot_access_pos_or_checkout(): void
    {
        $this->get(route('kasir.pos.index'))->assertRedirect(route('login'));
        foreach (['owner', 'koki', 'pelanggan'] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user)->get(route('kasir.pos.index'))->assertForbidden();
            $this->actingAs($user)->post(route('kasir.pos.checkout'), [])->assertForbidden();
        }
    }

    public function test_cashier_checkout_uses_database_prices_and_persists_cash_sale_snapshot_and_change(): void
    {
        $cashier = $this->userWithRole('kasir');
        $menu = $this->menu('Nasi Goreng', 12000);
        $this->actingAs($cashier);
        $token = $this->checkoutToken();

        $this->post(route('kasir.pos.checkout'), [
            'checkout_token' => $token,
            'items' => [['menu_id' => $menu->id, 'quantity' => 2, 'price' => 1, 'subtotal' => 2]],
            'total' => 1,
            'cash_received' => 30000,
        ])->assertRedirect();

        $sale = Sale::sole();
        $this->assertSame(24000, $sale->total);
        $this->assertSame(30000, $sale->cash_received);
        $this->assertSame(6000, $sale->change_due);
        $this->assertSame(Sale::STATUS_COMPLETED, $sale->status);
        $this->assertSame(Sale::PAYMENT_PAID, $sale->payment_status);
        $this->assertSame(Sale::METHOD_CASH, $sale->payment_method);
        $this->assertDatabaseHas('sale_items', ['sale_id' => $sale->id, 'menu_id' => $menu->id, 'menu_name' => 'Nasi Goreng', 'unit_price' => 12000, 'quantity' => 2, 'line_subtotal' => 24000]);
        $this->get(route('kasir.pos.receipt', $sale))->assertOk()->assertSee($sale->invoice_number)->assertSee('6.000');
    }

    public function test_empty_cart_invalid_quantity_unavailable_menu_and_insufficient_cash_are_rejected(): void
    {
        $this->actingAs($this->userWithRole('kasir'));
        $available = $this->menu();
        $unavailable = $this->menu('Tidak Tersedia', 5000, false);

        $base = ['checkout_token' => $this->checkoutToken(), 'cash_received' => 50000];
        $this->from(route('kasir.pos.index'))->post(route('kasir.pos.checkout'), $base)->assertSessionHasErrors('items');

        foreach ([0, -1, 1.5, 'abc'] as $quantity) {
            $this->from(route('kasir.pos.index'))->post(route('kasir.pos.checkout'), $base + ['items' => [['menu_id' => $available->id, 'quantity' => $quantity]]])->assertSessionHasErrors('items.0.quantity');
        }

        $this->from(route('kasir.pos.index'))->post(route('kasir.pos.checkout'), $base + ['items' => [['menu_id' => $unavailable->id, 'quantity' => 1]]])->assertSessionHasErrors('checkout');
        $this->from(route('kasir.pos.index'))->post(route('kasir.pos.checkout'), array_replace($base, ['items' => [['menu_id' => $available->id, 'quantity' => 1]], 'cash_received' => 1]))->assertSessionHasErrors('checkout');
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }

    public function test_repeated_checkout_with_same_token_returns_the_same_sale_and_different_payload_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('kasir'));
        $menu = $this->menu();
        $token = $this->checkoutToken();
        $payload = ['checkout_token' => $token, 'items' => [['menu_id' => $menu->id, 'quantity' => 1]], 'cash_received' => 15000];

        $this->post(route('kasir.pos.checkout'), $payload)->assertRedirect();
        $sale = Sale::sole();
        $this->post(route('kasir.pos.checkout'), $payload)->assertRedirect(route('kasir.pos.receipt', $sale));
        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('sale_items', 1);

        $this->from(route('kasir.pos.index'))->post(route('kasir.pos.checkout'), array_replace($payload, ['cash_received' => 16000]))->assertSessionHasErrors('checkout');
        $this->assertDatabaseCount('sales', 1);
    }

    public function test_failure_while_saving_sale_item_rolls_back_entire_checkout(): void
    {
        $this->actingAs($this->userWithRole('kasir'));
        $first = $this->menu('Menu A', 10000);
        $second = $this->menu('Menu B', 5000);
        $token = $this->checkoutToken();
        SaleItem::creating(function (SaleItem $item) use ($second): void {
            if ((int) $item->menu_id === $second->id) throw new RuntimeException('Simulated item failure.');
        });

        try {
            $this->withoutExceptionHandling()->post(route('kasir.pos.checkout'), [
                'checkout_token' => $token,
                'items' => [['menu_id' => $first->id, 'quantity' => 1], ['menu_id' => $second->id, 'quantity' => 1]],
                'cash_received' => 20000,
            ]);
            $this->fail('Kegagalan detail harus menggagalkan checkout seluruhnya.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated item failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }

    public function test_checkout_does_not_change_menu_or_ingredient_stock_or_movement_history(): void
    {
        $this->actingAs($this->userWithRole('kasir'));
        $menu = $this->menu();
        $unit = Unit::create(['name' => 'Gram', 'abbreviation' => 'g']);
        $ingredient = app(IngredientStockService::class)->createWithOpeningBalance([
            'unit_id' => $unit->id, 'name' => 'Beras', 'minimum_stock' => '1.000', 'average_cost_price' => '100.00', 'is_active' => true,
        ], '10.000', 'pos-test:ingredient-opening');
        $menu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.100']);
        $menuStock = $menu->stock;
        $ingredientStock = $ingredient->current_stock;
        $movements = $ingredient->stockMovements()->count();

        $this->post(route('kasir.pos.checkout'), ['checkout_token' => $this->checkoutToken(), 'items' => [['menu_id' => $menu->id, 'quantity' => 1]], 'cash_received' => 12000])->assertRedirect();

        $this->assertSame($menuStock, $menu->refresh()->stock);
        $this->assertSame($ingredientStock, $ingredient->refresh()->current_stock);
        $this->assertSame($movements, $ingredient->stockMovements()->count());
    }

    public function test_cashier_cannot_open_another_cashiers_receipt(): void
    {
        $first = $this->userWithRole('kasir');
        $other = $this->userWithRole('kasir');
        $sale = app(\App\Services\SaleService::class)->create([['menu_id' => $this->menu()->id, 'quantity' => 1]], $first->id);

        $this->actingAs($other)->get(route('kasir.pos.receipt', $sale))->assertNotFound();
    }

    public function test_cashier_history_search_is_paginated_and_limited_to_their_own_sales(): void
    {
        $cashier = $this->userWithRole('kasir');
        $otherCashier = $this->userWithRole('kasir');
        $menu = $this->menu('Soto Ayam', 14000);
        $ownSale = app(\App\Services\SaleService::class)->create([['menu_id' => $menu->id, 'quantity' => 1]], $cashier->id);
        $otherSale = app(\App\Services\SaleService::class)->create([['menu_id' => $menu->id, 'quantity' => 2]], $otherCashier->id);

        $this->actingAs($cashier)->get(route('kasir.pos.history', ['invoice' => substr($ownSale->invoice_number, -5), 'date' => now()->format('Y-m-d')]))
            ->assertOk()->assertSee($ownSale->invoice_number)->assertDontSee($otherSale->invoice_number);
        $this->get(route('kasir.pos.history'))->assertOk()->assertSee($ownSale->invoice_number)->assertDontSee($otherSale->invoice_number);
        $this->actingAs($this->userWithRole('owner'))->get(route('kasir.pos.history'))->assertForbidden();
    }
}
