<?php

namespace Tests\Feature\Sales;

use App\Models\Menu;
use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Unit;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\SaleService;
use App\Services\IngredientStockService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class SaleFoundationTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $name): User
    {
        $role = Role::firstOrCreate(['name' => $name]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function menu(string $name, int $price, bool $available = true): Menu
    {
        return Menu::create([
            'name' => $name,
            'category' => 'makanan',
            'price' => $price,
            'stock' => 10,
            'is_available' => $available,
        ]);
    }

    public function test_sale_snapshots_menu_name_and_price_and_calculates_totals_from_database(): void
    {
        $cashier = $this->userWithRole('kasir');
        $rice = $this->menu('Nasi Goreng', 12000);
        $tea = $this->menu('Es Teh', 5000);
        $unit = Unit::create(['name' => 'Gram', 'abbreviation' => 'g']);
        $ingredient = app(IngredientStockService::class)->createWithOpeningBalance([
            'unit_id' => $unit->id,
            'name' => 'Beras Bahan',
            'minimum_stock' => '1.000',
            'average_cost_price' => '10000.00',
            'is_active' => true,
        ], '20.000', 'sales-test:ingredient-opening');
        $rice->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.150']);
        $menuStockBefore = $rice->stock;
        $ingredientStockBefore = $ingredient->current_stock;
        $ingredientMovementCountBefore = $ingredient->stockMovements()->count();

        $sale = app(SaleService::class)->create([
            ['menu_id' => $rice->id, 'quantity' => 2, 'unit_price' => 1, 'line_subtotal' => 2],
            ['menu_id' => $tea->id, 'quantity' => 3, 'unit_price' => 1, 'line_subtotal' => 3],
        ], $cashier->id);

        $this->assertMatchesRegularExpression('/^INV-\d{8}-[A-Z0-9]{12}$/', $sale->invoice_number);
        $this->assertSame($cashier->id, $sale->cashier->id);
        $this->assertSame('kasir', $sale->cashier->role->name);
        $this->assertSame(39000, $sale->subtotal);
        $this->assertSame(39000, $sale->total);
        $this->assertSame(Sale::STATUS_PENDING, $sale->status);
        $this->assertSame(Sale::PAYMENT_UNPAID, $sale->payment_status);
        $this->assertNull($sale->payment_method);
        $this->assertCount(2, $sale->items);

        $riceItem = $sale->items->firstWhere('menu_id', $rice->id);
        $teaItem = $sale->items->firstWhere('menu_id', $tea->id);
        $this->assertSame('Nasi Goreng', $riceItem->menu_name);
        $this->assertSame(12000, $riceItem->unit_price);
        $this->assertSame(2, $riceItem->quantity);
        $this->assertSame(24000, $riceItem->line_subtotal);
        $this->assertSame('Es Teh', $teaItem->menu_name);
        $this->assertSame(5000, $teaItem->unit_price);
        $this->assertSame(15000, $teaItem->line_subtotal);
        $this->assertSame($menuStockBefore, $rice->refresh()->stock);
        $this->assertSame($ingredientStockBefore, $ingredient->refresh()->current_stock);
        $this->assertSame($ingredientMovementCountBefore, $ingredient->stockMovements()->count());

        $rice->update(['name' => 'Nasi Goreng Baru', 'price' => 15000]);
        $riceItem->refresh();
        $this->assertSame('Nasi Goreng', $riceItem->menu_name);
        $this->assertSame(12000, $riceItem->unit_price);
        $this->assertSame(24000, $riceItem->line_subtotal);
    }

    public function test_sale_and_detail_relations_and_foreign_key_delete_policies_preserve_history(): void
    {
        $cashier = $this->userWithRole('kasir');
        $menu = $this->menu('Bubur Ayam', 10000);
        $sale = app(SaleService::class)->create([['menu_id' => $menu->id, 'quantity' => 1]], $cashier->id);
        $item = $sale->items()->sole();

        $this->assertTrue($cashier->sales()->whereKey($sale->id)->exists());
        $this->assertTrue($item->sale->is($sale));
        $this->assertTrue($item->menu->is($menu));

        $menu->delete();
        $item->refresh();
        $this->assertNull($item->menu_id);
        $this->assertSame('Bubur Ayam', $item->menu_name);
        $this->assertSame(10000, $item->unit_price);

        try {
            $cashier->delete();
            $this->fail('Kasir dengan riwayat transaksi harus terlindungi oleh foreign key.');
        } catch (QueryException) {
            $this->assertDatabaseHas('users', ['id' => $cashier->id]);
        }

        try {
            $sale->delete();
            $this->fail('Sale dengan detail harus terlindungi oleh foreign key.');
        } catch (QueryException) {
            $this->assertDatabaseHas('sales', ['id' => $sale->id]);
            $this->assertDatabaseHas('sale_items', ['id' => $item->id]);
        }
    }

    public function test_invoice_number_is_unique(): void
    {
        $cashier = $this->userWithRole('kasir');
        $sale = app(SaleService::class)->create([['menu_id' => $this->menu('Soto', 14000)->id, 'quantity' => 1]], $cashier->id);

        try {
            $duplicate = new Sale();
            $duplicate->forceFill([
                'invoice_number' => $sale->invoice_number,
                'cashier_id' => $cashier->id,
                'subtotal' => 1,
                'total' => 1,
            ])->save();
            $this->fail('Nomor invoice duplikat seharusnya ditolak.');
        } catch (QueryException) {
            $this->assertDatabaseCount('sales', 1);
        }
    }

    public function test_sale_cannot_store_duplicate_menu_detail_rows(): void
    {
        $cashier = $this->userWithRole('kasir');
        $menu = $this->menu('Roti Bakar', 8000);
        $sale = app(SaleService::class)->create([['menu_id' => $menu->id, 'quantity' => 1]], $cashier->id);

        try {
            DB::table('sale_items')->insert([
                'sale_id' => $sale->id,
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'unit_price' => $menu->price,
                'quantity' => 1,
                'line_subtotal' => $menu->price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->fail('Detail menu duplikat pada satu transaksi seharusnya ditolak.');
        } catch (QueryException) {
            $this->assertDatabaseCount('sale_items', 1);
        }
    }

    public function test_sale_service_rejects_invalid_cashier_menu_quantity_and_duplicate_menu_items(): void
    {
        $cashier = $this->userWithRole('kasir');
        $owner = $this->userWithRole('owner');
        $availableMenu = $this->menu('Mie Ayam', 13000);
        $unavailableMenu = $this->menu('Menu Tutup', 9000, false);
        $service = app(SaleService::class);

        $cases = [
            fn () => $service->create([], $cashier->id),
            fn () => $service->create([['menu_id' => 999999, 'quantity' => 1]], $cashier->id),
            fn () => $service->create([['menu_id' => $unavailableMenu->id, 'quantity' => 1]], $cashier->id),
            fn () => $service->create([['menu_id' => $availableMenu->id, 'quantity' => 0]], $cashier->id),
            fn () => $service->create([['menu_id' => $availableMenu->id, 'quantity' => 1], ['menu_id' => $availableMenu->id, 'quantity' => 2]], $cashier->id),
            fn () => $service->create([['menu_id' => $availableMenu->id, 'quantity' => 1]], $owner->id),
        ];

        foreach ($cases as $case) {
            try {
                $case();
                $this->fail('Input transaksi yang tidak valid seharusnya ditolak.');
            } catch (InvalidArgumentException) {
                $this->assertDatabaseCount('sales', 0);
                $this->assertDatabaseCount('sale_items', 0);
            }
        }
    }

    public function test_failure_while_creating_a_detail_rolls_back_sale_and_all_items(): void
    {
        $cashier = $this->userWithRole('kasir');
        $firstMenu = $this->menu('Ayam Bakar', 18000);
        $secondMenu = $this->menu('Jus Jeruk', 8000);

        SaleItem::creating(function (SaleItem $item) use ($secondMenu): void {
            if ($item->menu_id === $secondMenu->id) {
                throw new RuntimeException('Simulated sale item insert failure.');
            }
        });

        try {
            app(SaleService::class)->create([
                ['menu_id' => $firstMenu->id, 'quantity' => 1],
                ['menu_id' => $secondMenu->id, 'quantity' => 1],
            ], $cashier->id);
            $this->fail('Kegagalan detail transaksi harus menggagalkan seluruh transaksi.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated sale item insert failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }
}
