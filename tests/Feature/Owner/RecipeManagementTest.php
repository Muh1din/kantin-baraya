<?php

namespace Tests\Feature\Owner;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\IngredientStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RecipeManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $name): User
    {
        $role = Role::firstOrCreate(['name' => $name]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function menu(string $name = 'Nasi Goreng'): Menu
    {
        return Menu::create([
            'name' => $name,
            'category' => 'makanan',
            'price' => 15000,
            'stock' => 0,
            'is_available' => true,
        ]);
    }

    private function ingredient(string $name, string $openingStock = '8.000', bool $active = true): Ingredient
    {
        $unit = Unit::create([
            'name' => 'Satuan ' . $name,
            'abbreviation' => 'u' . substr(md5($name), 0, 6),
        ]);

        return app(IngredientStockService::class)->createWithOpeningBalance([
            'unit_id' => $unit->id,
            'name' => $name,
            'minimum_stock' => '1.000',
            'average_cost_price' => '100.00',
            'is_active' => $active,
        ], $openingStock, 'recipe-test:opening:' . $name);
    }

    public function test_owner_can_view_menu_recipe_list_and_status(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menuWithoutRecipe = $this->menu();
        $menuWithRecipe = $this->menu('Mie Goreng');
        $ingredient = $this->ingredient('Minyak');
        $menuWithRecipe->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.050']);

        $this->get(route('owner.recipes.index'))
            ->assertOk()
            ->assertSee($menuWithoutRecipe->name)
            ->assertSee($menuWithRecipe->name)
            ->assertSee('Belum dibuat')
            ->assertSee('1 bahan');
    }

    public function test_owner_can_add_multiple_active_ingredients_and_units_are_shown(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menu = $this->menu();
        $rice = $this->ingredient('Beras', '8.000');
        $oil = $this->ingredient('Minyak', '4.000');
        $riceStockBefore = $rice->current_stock;
        $oilStockBefore = $oil->current_stock;
        $riceMovementCountBefore = $rice->stockMovements()->count();
        $oilMovementCountBefore = $oil->stockMovements()->count();

        $this->post(route('owner.recipes.ingredients.store', $menu), [
            'ingredients' => [
                ['ingredient_id' => $rice->id, 'quantity' => '0.150'],
                ['ingredient_id' => $oil->id, 'quantity' => '0.020'],
            ],
        ])->assertRedirect(route('owner.recipes.show', $menu));

        $this->assertDatabaseHas('menu_ingredients', ['menu_id' => $menu->id, 'ingredient_id' => $rice->id, 'quantity' => '0.150']);
        $this->assertDatabaseHas('menu_ingredients', ['menu_id' => $menu->id, 'ingredient_id' => $oil->id, 'quantity' => '0.020']);
        $this->assertSame($riceStockBefore, $rice->refresh()->current_stock);
        $this->assertSame($oilStockBefore, $oil->refresh()->current_stock);
        $this->assertSame($riceMovementCountBefore, $rice->stockMovements()->count());
        $this->assertSame($oilMovementCountBefore, $oil->stockMovements()->count());
        $this->get(route('owner.recipes.show', $menu))
            ->assertOk()
            ->assertSee('Beras')
            ->assertSee('Minyak')
            ->assertSee($rice->unit->abbreviation)
            ->assertSee($oil->unit->abbreviation);
    }

    public function test_owner_can_update_and_delete_a_composition_without_changing_inventory(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menu = $this->menu();
        $ingredient = $this->ingredient('Garam');
        $composition = $menu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.010']);
        $stockBefore = $ingredient->current_stock;
        $movementCountBefore = $ingredient->stockMovements()->count();

        $this->put(route('owner.recipes.ingredients.update', [$menu, $composition]), ['quantity' => '0.015'])
            ->assertRedirect(route('owner.recipes.show', $menu));
        $this->assertDatabaseHas('menu_ingredients', ['id' => $composition->id, 'quantity' => '0.015']);

        $this->delete(route('owner.recipes.ingredients.destroy', [$menu, $composition]))
            ->assertRedirect(route('owner.recipes.show', $menu));

        $this->assertDatabaseMissing('menu_ingredients', ['id' => $composition->id]);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id]);
        $this->assertSame($stockBefore, $ingredient->refresh()->current_stock);
        $this->assertSame($movementCountBefore, $ingredient->stockMovements()->count());
    }

    public function test_zero_negative_invalid_and_inactive_ingredients_are_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menu = $this->menu();
        $active = $this->ingredient('Gula');
        $inactive = $this->ingredient('Bahan Nonaktif', active: false);

        foreach (['0', '-0.010'] as $invalidQuantity) {
            $this->from(route('owner.recipes.show', $menu))
                ->post(route('owner.recipes.ingredients.store', $menu), [
                    'ingredients' => [['ingredient_id' => $active->id, 'quantity' => $invalidQuantity]],
                ])
                ->assertSessionHasErrors('ingredients.0.quantity');
        }

        $this->from(route('owner.recipes.show', $menu))
            ->post(route('owner.recipes.ingredients.store', $menu), [
                'ingredients' => [['ingredient_id' => 999999, 'quantity' => '0.010']],
            ])
            ->assertSessionHasErrors('ingredients.0.ingredient_id');

        $this->from(route('owner.recipes.show', $menu))
            ->post(route('owner.recipes.ingredients.store', $menu), [
                'ingredients' => [['ingredient_id' => $inactive->id, 'quantity' => '0.010']],
            ])
            ->assertSessionHasErrors('ingredients.0.ingredient_id');

        $this->assertDatabaseCount('menu_ingredients', 0);
    }

    public function test_same_ingredient_cannot_be_added_twice_in_a_request_or_existing_recipe(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menu = $this->menu();
        $ingredient = $this->ingredient('Kecap');

        $this->from(route('owner.recipes.show', $menu))
            ->post(route('owner.recipes.ingredients.store', $menu), [
                'ingredients' => [
                    ['ingredient_id' => $ingredient->id, 'quantity' => '0.010'],
                    ['ingredient_id' => $ingredient->id, 'quantity' => '0.020'],
                ],
            ])
            ->assertSessionHasErrors('ingredients.1.ingredient_id');
        $this->assertDatabaseCount('menu_ingredients', 0);

        $menu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.010']);
        $this->from(route('owner.recipes.show', $menu))
            ->post(route('owner.recipes.ingredients.store', $menu), [
                'ingredients' => [['ingredient_id' => $ingredient->id, 'quantity' => '0.020']],
            ])
            ->assertSessionHasErrors('ingredients');
        $this->assertDatabaseCount('menu_ingredients', 1);
    }

    public function test_batch_failure_rolls_back_all_new_compositions(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menu = $this->menu();
        $first = $this->ingredient('Bawang');
        $second = $this->ingredient('Cabai');

        MenuIngredient::creating(function (MenuIngredient $composition) use ($second): void {
            if ($composition->ingredient_id === $second->id) {
                throw new RuntimeException('Simulated composition insert failure.');
            }
        });

        try {
            $this->withoutExceptionHandling()->post(route('owner.recipes.ingredients.store', $menu), [
                'ingredients' => [
                    ['ingredient_id' => $first->id, 'quantity' => '0.010'],
                    ['ingredient_id' => $second->id, 'quantity' => '0.005'],
                ],
            ]);
            $this->fail('Penyimpanan batch seharusnya gagal pada komposisi kedua.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated composition insert failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('menu_ingredients', 0);
        $this->assertSame('8.000', $first->refresh()->current_stock);
        $this->assertSame('8.000', $second->refresh()->current_stock);
    }

    public function test_non_owner_cannot_access_recipe_routes(): void
    {
        $menu = $this->menu();
        $ingredient = $this->ingredient('Saus');
        $composition = $menu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.010']);
        $kasir = $this->userWithRole('kasir');

        $this->actingAs($kasir)->get(route('owner.recipes.index'))->assertForbidden();
        $this->actingAs($kasir)->get(route('owner.recipes.show', $menu))->assertForbidden();
        $this->actingAs($kasir)->post(route('owner.recipes.ingredients.store', $menu), [])->assertForbidden();
        $this->actingAs($kasir)->put(route('owner.recipes.ingredients.update', [$menu, $composition]), [])->assertForbidden();
        $this->actingAs($kasir)->delete(route('owner.recipes.ingredients.destroy', [$menu, $composition]))->assertForbidden();
    }

    public function test_missing_menu_or_composition_is_not_found(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menu = $this->menu();

        $this->get(route('owner.recipes.show', 999999))->assertNotFound();
        $this->post(route('owner.recipes.ingredients.store', 999999), [])->assertNotFound();
        $this->put(route('owner.recipes.ingredients.update', [$menu, 999999]), ['quantity' => '0.010'])->assertNotFound();
        $this->delete(route('owner.recipes.ingredients.destroy', [$menu, 999999]))->assertNotFound();
    }

    public function test_composition_from_another_menu_cannot_be_updated_or_deleted(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $firstMenu = $this->menu('Menu A');
        $secondMenu = $this->menu('Menu B');
        $ingredient = $this->ingredient('Merica');
        $composition = $firstMenu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.010']);

        $this->put(route('owner.recipes.ingredients.update', [$secondMenu, $composition]), ['quantity' => '0.020'])->assertNotFound();
        $this->delete(route('owner.recipes.ingredients.destroy', [$secondMenu, $composition]))->assertNotFound();
        $this->assertDatabaseHas('menu_ingredients', ['id' => $composition->id, 'menu_id' => $firstMenu->id, 'quantity' => '0.010']);
    }
}
