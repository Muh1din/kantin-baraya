<?php

namespace Tests\Feature\Chef;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\IngredientStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChefInventoryConsumptionTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $name): User
    {
        $role = Role::firstOrCreate(['name' => $name]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function menu(string $name): Menu
    {
        return Menu::create([
            'name' => $name,
            'category' => 'makanan',
            'price' => 12000,
            'stock' => 10,
            'is_available' => true,
        ]);
    }

    private function ingredient(string $name, string $openingStock): Ingredient
    {
        $unit = Unit::create([
            'name' => 'Unit ' . $name,
            'abbreviation' => 'u' . substr(md5($name), 0, 6),
        ]);

        return app(IngredientStockService::class)->createWithOpeningBalance([
            'unit_id' => $unit->id,
            'name' => $name,
            'minimum_stock' => '0.000',
            'average_cost_price' => '100.00',
            'is_active' => true,
        ], $openingStock, 'chef-inventory:opening:' . $name);
    }

    private function confirmedOrder(array $items): Order
    {
        $order = Order::create([
            'order_code' => 'ORD-CHEF-' . uniqid(),
            'tracking_token' => 'token-' . uniqid(),
            'customer_name' => 'Customer',
            'order_channel' => 'online',
            'order_type' => 'bungkus',
            'order_status' => 'confirmed',
            'subtotal' => 12000,
            'total_amount' => 12000,
        ]);

        foreach ($items as [$menu, $quantity]) {
            $order->items()->create([
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'quantity' => $quantity,
                'unit_price' => $menu->price,
                'subtotal' => $menu->price * $quantity,
            ]);
        }

        return $order;
    }

    public function test_starting_order_consumes_recipe_for_quantities_across_multiple_menus(): void
    {
        $chef = $this->userWithRole('koki');
        $rice = $this->ingredient('Beras', '10.000');
        $chicken = $this->ingredient('Ayam', '10.000');
        $vegetable = $this->ingredient('Sayur', '10.000');
        $menuA = $this->menu('Nasi Ayam');
        $menuB = $this->menu('Nasi Sayur');
        $menuA->recipeIngredients()->create(['ingredient_id' => $rice->id, 'quantity' => '0.100']);
        $menuA->recipeIngredients()->create(['ingredient_id' => $chicken->id, 'quantity' => '0.200']);
        $menuB->recipeIngredients()->create(['ingredient_id' => $rice->id, 'quantity' => '0.050']);
        $menuB->recipeIngredients()->create(['ingredient_id' => $vegetable->id, 'quantity' => '0.100']);
        $order = $this->confirmedOrder([[$menuA, 2], [$menuB, 3]]);

        $this->actingAs($chef)->post(route('chef.orders.start', $order))->assertRedirect();

        $this->assertSame('in_progress', $order->refresh()->order_status);
        $this->assertSame('9.650', $rice->refresh()->current_stock);
        $this->assertSame('9.600', $chicken->refresh()->current_stock);
        $this->assertSame('9.700', $vegetable->refresh()->current_stock);
        $this->assertDatabaseHas('ingredient_stock_movements', [
            'order_id' => $order->id,
            'ingredient_id' => $rice->id,
            'movement_type' => 'stock_out',
            'quantity_change' => '-0.350',
        ]);
    }

    public function test_insufficient_stock_keeps_order_and_all_ingredient_balances_unchanged(): void
    {
        $chef = $this->userWithRole('koki');
        $first = $this->ingredient('First', '5.000');
        $short = $this->ingredient('Short', '0.050');
        $menu = $this->menu('Menu');
        $menu->recipeIngredients()->create(['ingredient_id' => $first->id, 'quantity' => '1.000']);
        $menu->recipeIngredients()->create(['ingredient_id' => $short->id, 'quantity' => '0.100']);
        $order = $this->confirmedOrder([[$menu, 1]]);

        $this->actingAs($chef)
            ->from(route('chef.dashboard'))
            ->post(route('chef.orders.start', $order))
            ->assertRedirect(route('chef.dashboard'))
            ->assertSessionHas('error');

        $this->assertSame('confirmed', $order->refresh()->order_status);
        $this->assertSame('5.000', $first->refresh()->current_stock);
        $this->assertSame('0.050', $short->refresh()->current_stock);
        $this->assertDatabaseMissing('ingredient_stock_movements', ['order_id' => $order->id]);
    }

    public function test_repeated_start_action_does_not_consume_stock_twice(): void
    {
        $chef = $this->userWithRole('koki');
        $ingredient = $this->ingredient('Oil', '2.000');
        $menu = $this->menu('Fried Rice');
        $menu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.250']);
        $order = $this->confirmedOrder([[$menu, 2]]);
        $this->actingAs($chef);

        $this->post(route('chef.orders.start', $order))->assertRedirect();
        $this->post(route('chef.orders.start', $order))->assertRedirect()->assertSessionHas('error');

        $this->assertSame('1.500', $ingredient->refresh()->current_stock);
        $this->assertSame(1, IngredientStockMovement::query()
            ->where('order_id', $order->id)
            ->where('movement_type', 'stock_out')
            ->count());
    }

    public function test_cancelling_in_progress_order_restores_stock_with_new_referenced_movements(): void
    {
        $chef = $this->userWithRole('koki');
        $ingredient = $this->ingredient('Sugar', '3.000');
        $menu = $this->menu('Tea');
        $menu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.125']);
        $order = $this->confirmedOrder([[$menu, 2]]);
        $this->actingAs($chef)->post(route('chef.orders.start', $order));

        $this->post(route('chef.orders.cancel', $order))->assertRedirect();

        $this->assertSame('cancelled', $order->refresh()->order_status);
        $this->assertSame('3.000', $ingredient->refresh()->current_stock);
        $this->assertDatabaseHas('ingredient_stock_movements', [
            'order_id' => $order->id,
            'movement_type' => 'stock_out',
            'quantity_change' => '-0.250',
        ]);
        $this->assertDatabaseHas('ingredient_stock_movements', [
            'order_id' => $order->id,
            'movement_type' => 'stock_in',
            'quantity_change' => '0.250',
        ]);
        $this->assertSame(3, $ingredient->stockMovements()->count());
    }

    public function test_failure_recording_one_ingredient_movement_rolls_back_all_depletions(): void
    {
        $chef = $this->userWithRole('koki');
        $first = $this->ingredient('First', '5.000');
        $second = $this->ingredient('Second', '5.000');
        $menu = $this->menu('Menu');
        $menu->recipeIngredients()->create(['ingredient_id' => $first->id, 'quantity' => '1.000']);
        $menu->recipeIngredients()->create(['ingredient_id' => $second->id, 'quantity' => '1.000']);
        $order = $this->confirmedOrder([[$menu, 1]]);

        IngredientStockMovement::creating(function (IngredientStockMovement $movement) use ($second): void {
            if ($movement->order_id && $movement->ingredient_id === $second->id) {
                throw new \RuntimeException('Simulated stock movement failure.');
            }
        });

        try {
            $this->withoutExceptionHandling()->actingAs($chef)->post(route('chef.orders.start', $order));
            $this->fail('Pergerakan bahan kedua seharusnya menggagalkan seluruh proses.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated stock movement failure.', $exception->getMessage());
        }

        $this->assertSame('confirmed', $order->refresh()->order_status);
        $this->assertSame('5.000', $first->refresh()->current_stock);
        $this->assertSame('5.000', $second->refresh()->current_stock);
        $this->assertDatabaseMissing('ingredient_stock_movements', ['order_id' => $order->id]);
    }

    public function test_recipe_edits_after_order_creation_only_affect_later_orders(): void
    {
        $chef = $this->userWithRole('koki');
        $ingredient = $this->ingredient('Flour', '5.000');
        $menu = $this->menu('Bread');
        $composition = $menu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.100']);
        $oldOrder = $this->confirmedOrder([[$menu, 2]]);

        $composition->update(['quantity' => '0.500']);
        $newOrder = $this->confirmedOrder([[$menu, 1]]);

        $this->actingAs($chef)->post(route('chef.orders.start', $oldOrder))->assertRedirect();
        $this->assertSame('4.800', $ingredient->refresh()->current_stock);
        $this->actingAs($chef)->post(route('chef.orders.start', $newOrder))->assertRedirect();

        $this->assertSame('4.300', $ingredient->refresh()->current_stock);
        $this->assertDatabaseHas('ingredient_stock_movements', [
            'order_id' => $oldOrder->id,
            'quantity_change' => '-0.200',
        ]);
        $this->assertDatabaseHas('ingredient_stock_movements', [
            'order_id' => $newOrder->id,
            'quantity_change' => '-0.500',
        ]);
    }

    public function test_legacy_order_without_recipe_snapshot_is_blocked_instead_of_using_current_recipe(): void
    {
        $chef = $this->userWithRole('koki');
        $ingredient = $this->ingredient('Spice', '2.000');
        $menu = $this->menu('Legacy Menu');
        $menu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.200']);
        $order = $this->confirmedOrder([[$menu, 1]]);
        DB::table('order_items')->where('order_id', $order->id)->update(['recipe_snapshotted_at' => null]);

        $this->actingAs($chef)
            ->from(route('chef.dashboard'))
            ->post(route('chef.orders.start', $order))
            ->assertRedirect(route('chef.dashboard'))
            ->assertSessionHas('error');

        $this->assertSame('confirmed', $order->refresh()->order_status);
        $this->assertSame('2.000', $ingredient->refresh()->current_stock);
        $this->assertDatabaseMissing('ingredient_stock_movements', ['order_id' => $order->id]);
    }

    public function test_owner_sidebar_provides_links_to_ingredients_and_recipes(): void
    {
        $owner = $this->userWithRole('owner');

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee(route('owner.ingredients.index'))
            ->assertSee(route('owner.recipes.index'));
    }
}
