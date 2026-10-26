<?php

namespace Tests\Feature\Owner;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\IngredientStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class IngredientStockMovementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $name): User
    {
        $role = Role::firstOrCreate(['name' => $name]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function ingredient(string $openingStock = '5.000', bool $active = true): Ingredient
    {
        $unit = Unit::create(['name' => 'Kilogram', 'abbreviation' => 'kg']);

        return app(IngredientStockService::class)->createWithOpeningBalance([
            'unit_id' => $unit->id,
            'name' => 'Beras',
            'minimum_stock' => '1.000',
            'average_cost_price' => '15000.00',
            'is_active' => $active,
        ], $openingStock, 'test:opening:' . uniqid());
    }

    private function formToken(string $movementType): string
    {
        $tokens = session('owner.stock_movement_tokens', []);

        foreach (array_reverse($tokens, true) as $token => $entry) {
            if (($entry['movement_type'] ?? null) === $movementType) {
                return $token;
            }
        }

        $this->fail('Token form tidak ditemukan di session.');
    }

    public function test_owner_can_record_stock_in_and_balance_matches_movement(): void
    {
        $owner = $this->userWithRole('owner');
        $this->actingAs($owner);
        $ingredient = $this->ingredient();
        $this->get(route('owner.ingredients.index'))->assertOk();
        $token = $this->formToken(IngredientStockMovement::TYPE_STOCK_IN);

        $this->post(route('owner.ingredients.stock-in'), [
            'stock_in_ingredient_id' => $ingredient->id,
            'stock_in_quantity' => '2.125',
            'stock_in_notes' => 'Pembelian bahan',
            'stock_in_token' => $token,
        ])->assertRedirect(route('owner.ingredients.index'));

        $ingredient->refresh();
        $movement = $ingredient->stockMovements()->where('movement_type', IngredientStockMovement::TYPE_STOCK_IN)->sole();
        $this->assertSame('7.125', $ingredient->current_stock);
        $this->assertSame('2.125', $movement->quantity_change);
        $this->assertSame('7.125', $movement->stock_after);
        $this->assertSame('Pembelian bahan', $movement->notes);
        $this->assertSame($owner->id, $movement->user_id);
    }

    public function test_owner_can_make_positive_or_negative_adjustment(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $ingredient = $this->ingredient();
        $this->get(route('owner.ingredients.index'))->assertOk();
        $token = $this->formToken(IngredientStockMovement::TYPE_ADJUSTMENT);

        $this->post(route('owner.ingredients.adjustment'), [
            'adjustment_ingredient_id' => $ingredient->id,
            'adjustment_quantity' => '-1.250',
            'adjustment_reason' => 'Hasil hitung fisik',
            'adjustment_token' => $token,
        ])->assertRedirect(route('owner.ingredients.index'));

        $ingredient->refresh();
        $movement = $ingredient->stockMovements()
            ->where('movement_type', IngredientStockMovement::TYPE_ADJUSTMENT)
            ->where('quantity_change', '-1.250')
            ->sole();
        $this->assertSame('3.750', $ingredient->current_stock);
        $this->assertSame('-1.250', $movement->quantity_change);
        $this->assertSame('3.750', $movement->stock_after);
        $this->assertSame('Hasil hitung fisik', $movement->notes);

        $this->get(route('owner.ingredients.index'))->assertOk();
        $positiveToken = $this->formToken(IngredientStockMovement::TYPE_ADJUSTMENT);
        $this->post(route('owner.ingredients.adjustment'), [
            'adjustment_ingredient_id' => $ingredient->id,
            'adjustment_quantity' => '0.500',
            'adjustment_reason' => 'Koreksi hasil timbang',
            'adjustment_token' => $positiveToken,
        ])->assertRedirect(route('owner.ingredients.index'));

        $this->assertSame('4.250', $ingredient->refresh()->current_stock);
        $this->assertSame(3, $ingredient->stockMovements()->count());
    }

    public function test_invalid_stock_in_adjustment_and_inactive_stock_in_are_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $ingredient = $this->ingredient(active: false);
        $this->get(route('owner.ingredients.index'))->assertOk();
        $stockInToken = $this->formToken(IngredientStockMovement::TYPE_STOCK_IN);
        $adjustmentToken = $this->formToken(IngredientStockMovement::TYPE_ADJUSTMENT);

        $this->from(route('owner.ingredients.index'))
            ->post(route('owner.ingredients.stock-in'), [
                'stock_in_ingredient_id' => $ingredient->id,
                'stock_in_quantity' => '0',
                'stock_in_token' => $stockInToken,
            ])
            ->assertSessionHasErrors(['stock_in_ingredient_id', 'stock_in_quantity']);

        $this->from(route('owner.ingredients.index'))
            ->post(route('owner.ingredients.adjustment'), [
                'adjustment_ingredient_id' => 999999,
                'adjustment_quantity' => '0.000',
                'adjustment_reason' => '',
                'adjustment_token' => $adjustmentToken,
            ])
            ->assertSessionHasErrors(['adjustment_ingredient_id', 'adjustment_quantity', 'adjustment_reason']);

        $this->assertSame('5.000', $ingredient->refresh()->current_stock);
        $this->assertSame(1, $ingredient->stockMovements()->count());
    }

    public function test_adjustment_that_would_make_balance_negative_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $ingredient = $this->ingredient('1.000');
        $this->get(route('owner.ingredients.index'))->assertOk();
        $token = $this->formToken(IngredientStockMovement::TYPE_ADJUSTMENT);

        $this->from(route('owner.ingredients.index'))
            ->post(route('owner.ingredients.adjustment'), [
                'adjustment_ingredient_id' => $ingredient->id,
                'adjustment_quantity' => '-1.001',
                'adjustment_reason' => 'Koreksi hitung fisik',
                'adjustment_token' => $token,
            ])
            ->assertSessionHasErrors('adjustment_quantity');

        $this->assertSame('1.000', $ingredient->refresh()->current_stock);
        $this->assertSame(1, $ingredient->stockMovements()->count());
    }

    public function test_same_form_submission_is_idempotent_and_key_reuse_with_other_payload_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $ingredient = $this->ingredient();
        $this->get(route('owner.ingredients.index'))->assertOk();
        $token = $this->formToken(IngredientStockMovement::TYPE_STOCK_IN);
        $payload = [
            'stock_in_ingredient_id' => $ingredient->id,
            'stock_in_quantity' => '1.000',
            'stock_in_notes' => 'Penerimaan',
            'stock_in_token' => $token,
        ];

        $this->post(route('owner.ingredients.stock-in'), $payload)->assertRedirect(route('owner.ingredients.index'));
        $movementId = $ingredient->stockMovements()->where('movement_type', IngredientStockMovement::TYPE_STOCK_IN)->value('id');
        $this->post(route('owner.ingredients.stock-in'), $payload)->assertRedirect(route('owner.ingredients.index'));

        $this->assertSame('6.000', $ingredient->refresh()->current_stock);
        $this->assertSame(2, $ingredient->stockMovements()->count());
        $this->assertDatabaseHas('ingredient_stock_movements', ['id' => $movementId]);

        $payload['stock_in_quantity'] = '2.000';
        $this->from(route('owner.ingredients.index'))
            ->post(route('owner.ingredients.stock-in'), $payload)
            ->assertSessionHasErrors('stock_in_quantity');
        $this->assertSame('6.000', $ingredient->refresh()->current_stock);
        $this->assertSame(2, $ingredient->stockMovements()->count());
    }

    public function test_failure_while_creating_movement_rolls_back_balance_change(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $ingredient = $this->ingredient();
        $this->get(route('owner.ingredients.index'))->assertOk();
        $token = $this->formToken(IngredientStockMovement::TYPE_STOCK_IN);

        IngredientStockMovement::creating(function (IngredientStockMovement $movement): void {
            if ($movement->notes === 'force movement failure') {
                throw new RuntimeException('Simulated movement insert failure.');
            }
        });

        try {
            $this->withoutExceptionHandling()->post(route('owner.ingredients.stock-in'), [
                'stock_in_ingredient_id' => $ingredient->id,
                'stock_in_quantity' => '2.000',
                'stock_in_notes' => 'force movement failure',
                'stock_in_token' => $token,
            ]);
            $this->fail('Kegagalan pembuatan pergerakan seharusnya diteruskan setelah transaksi rollback.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated movement insert failure.', $exception->getMessage());
        }

        $this->assertSame('5.000', $ingredient->refresh()->current_stock);
        $this->assertSame(1, $ingredient->stockMovements()->count());
    }

    public function test_only_owner_can_access_stock_movement_endpoints(): void
    {
        $ingredient = $this->ingredient();
        $kasir = $this->userWithRole('kasir');

        $this->actingAs($kasir)->post(route('owner.ingredients.stock-in'), [])->assertForbidden();
        $this->actingAs($kasir)->post(route('owner.ingredients.adjustment'), [])->assertForbidden();
        auth()->logout();
        $this->post(route('owner.ingredients.stock-in'), [])->assertRedirect(route('login'));
        $this->post(route('owner.ingredients.adjustment'), [])->assertRedirect(route('login'));

        $this->assertSame('5.000', $ingredient->refresh()->current_stock);
        $this->assertSame(1, $ingredient->stockMovements()->count());
    }
}
