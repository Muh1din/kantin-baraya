<?php

namespace Tests\Feature\Cashier;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Unit;
use App\Models\User;
use App\Services\IngredientStockService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_cashier_checkout_uses_database_prices_and_persists_cash_order_snapshot(): void
    {
        $cashier = $this->userWithRole('kasir');
        $menu = $this->menu('Nasi Goreng', 12000);
        $this->actingAs($cashier);
        $token = $this->checkoutToken();

        $this->post(route('kasir.pos.checkout'), [
            'checkout_token' => $token,
            'customer_name' => 'John Doe',
            'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
            'cash_received' => 30000,
            'payment_method' => 'cash',
            'payment_timing' => 'now',
            'order_type' => 'makan_di_tempat',
        ])->assertRedirect();

        $order = Order::sole();
        $this->assertSame(24000, (int)$order->total_amount);
        $this->assertSame('confirmed', $order->order_status);
        $payment = $order->payment->first();
        $this->assertNotNull($payment);
        $this->assertSame(24000, (int)$payment->amount);
        $this->assertSame('paid', $payment->payment_status);
        $this->assertSame('cash', $payment->payment_method);
        $this->assertSame(30000, (int) $payment->cash_received);
        $this->assertSame(6000, (int) $payment->change_due);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'menu_id' => $menu->id, 'menu_name' => 'Nasi Goreng', 'unit_price' => 12000, 'quantity' => 2, 'subtotal' => 24000]);
        $this->get(route('kasir.pos.receipt', $order))->assertOk()->assertSee($order->order_code);
    }

    public function test_pay_after_meal_keeps_one_order_and_settles_its_existing_payment(): void
    {
        $cashier = $this->userWithRole('kasir');
        $menu = $this->menu('Mie Ayam', 15000);
        $this->actingAs($cashier);
        $this->post(route('kasir.pos.checkout'), [
            'checkout_token' => $this->checkoutToken(),
            'customer_name' => 'Dina',
            'items' => [['menu_id' => $menu->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'payment_timing' => 'after_meal',
            'order_type' => 'makan_di_tempat',
        ])->assertRedirect();

        $order = Order::sole();
        $payment = $order->payment()->sole();
        $this->assertSame('confirmed', $order->order_status);
        $this->assertSame('pending', $payment->payment_status);
        $this->assertDatabaseCount('orders', 1);

        $chef = $this->userWithRole('koki');
        $this->actingAs($chef)->get(route('chef.dashboard'))->assertOk()->assertSee($order->order_code);
        $this->post(route('chef.orders.start', $order))->assertRedirect();
        $this->get(route('chef.dashboard', ['status' => 'in_progress']))->assertOk()->assertSee($order->order_code);
        $this->post(route('chef.orders.complete', $order))->assertRedirect();
        $this->assertSame('completed', $order->refresh()->order_status);
        $this->assertSame('pending', $payment->refresh()->payment_status);

        $this->actingAs($cashier);
        $this->post(route('kasir.pos.settle', $order), [
            'cash_received' => 20000,
            'manual_verification' => '1',
        ])->assertRedirect(route('kasir.pos.receipt', $order));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payment_status' => 'paid',
            'cash_received' => 20000,
            'change_due' => 5000,
        ]);
        $this->assertSame('completed', $order->refresh()->order_status);
    }

    public function test_qris_pos_payment_remains_pending_until_cashier_manual_verification(): void
    {
        Storage::fake('public');
        $cashier = $this->userWithRole('kasir');
        $menu = $this->menu('Es Teh', 5000);
        $this->actingAs($cashier)->post(route('kasir.pos.checkout'), [
            'checkout_token' => $this->checkoutToken(),
            'customer_name' => 'Rani',
            'items' => [['menu_id' => $menu->id, 'quantity' => 1]],
            'payment_method' => 'qris',
            'payment_timing' => 'now',
            'order_type' => 'makan_di_tempat',
        ])->assertRedirect();

        $order = Order::sole();
        $payment = $order->payment()->sole();
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('pending', $payment->payment_status);

        $this->post(route('kasir.pos.settle', $order), [
            'proof_image' => UploadedFile::fake()->image('qris-proof.jpg'),
            'manual_verification' => '1',
        ])->assertRedirect(route('kasir.pos.receipt', $order));

        $this->assertSame('confirmed', $order->refresh()->order_status);
        $this->assertSame('paid', $payment->refresh()->payment_status);
        Storage::disk('public')->assertExists($payment->proof_image);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_owner_sales_report_lists_an_order_once_even_if_it_has_multiple_payments(): void
    {
        $cashier = $this->userWithRole('kasir');
        $menu = $this->menu('Bubur Ayam', 12000);
        $order = app(OrderService::class)->create(
            [['menu_id' => $menu->id, 'quantity' => 1]],
            $cashier->id,
            null,
            12000,
            'Tari',
        );
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'amount' => 12000,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->userWithRole('owner'))->get(route('owner.reports.sales'));
        $response->assertOk()->assertSee('Laporan Penjualan')->assertSee('1');
        $this->assertSame(1, substr_count($response->getContent(), $order->order_code));
    }

    public function test_empty_cart_invalid_quantity_unavailable_menu_and_insufficient_cash_are_rejected(): void
    {
        $this->actingAs($this->userWithRole('kasir'));
        $available = $this->menu();
        $unavailable = $this->menu('Tidak Tersedia', 5000, false);

        $base = ['checkout_token' => $this->checkoutToken(), 'cash_received' => 50000, 'customer_name' => 'John', 'payment_method' => 'cash', 'payment_timing' => 'now', 'order_type' => 'makan_di_tempat'];
        $this->from(route('kasir.pos.index'))->post(route('kasir.pos.checkout'), $base)->assertSessionHasErrors('items');

        foreach ([0, -1, 1.5, 'abc'] as $quantity) {
            $this->from(route('kasir.pos.index'))->post(route('kasir.pos.checkout'), $base + ['items' => [['menu_id' => $available->id, 'quantity' => $quantity]]])->assertSessionHasErrors('items.0.quantity');
        }

        $this->from(route('kasir.pos.index'))->post(route('kasir.pos.checkout'), $base + ['items' => [['menu_id' => $unavailable->id, 'quantity' => 1]]])->assertSessionHasErrors('checkout');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_repeated_checkout_with_same_token_returns_the_same_order_and_different_payload_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('kasir'));
        $menu = $this->menu();
        $token = $this->checkoutToken();
        $payload = ['checkout_token' => $token, 'customer_name' => 'John', 'items' => [['menu_id' => $menu->id, 'quantity' => 1]], 'cash_received' => 15000, 'payment_method' => 'cash', 'payment_timing' => 'now', 'order_type' => 'makan_di_tempat'];

        $this->post(route('kasir.pos.checkout'), $payload)->assertRedirect();
        $order = Order::sole();
        $this->post(route('kasir.pos.checkout'), $payload)->assertRedirect(route('kasir.pos.receipt', $order));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
    }

    public function test_failure_while_saving_order_item_rolls_back_entire_checkout(): void
    {
        $this->actingAs($this->userWithRole('kasir'));
        $first = $this->menu('Menu A', 10000);
        $second = $this->menu('Menu B', 5000);
        $token = $this->checkoutToken();
        OrderItem::creating(function (OrderItem $item) use ($second): void {
            if ((int) $item->menu_id === $second->id) throw new RuntimeException('Simulated item failure.');
        });

        try {
            $this->withoutExceptionHandling()->post(route('kasir.pos.checkout'), [
                'checkout_token' => $token,
                'customer_name' => 'John',
                'items' => [['menu_id' => $first->id, 'quantity' => 1], ['menu_id' => $second->id, 'quantity' => 1]],
                'cash_received' => 20000,
                'payment_method' => 'cash',
                'payment_timing' => 'now',
                'order_type' => 'makan_di_tempat',
            ]);
            $this->fail('Kegagalan detail harus menggagalkan checkout seluruhnya.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated item failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_checkout_does_not_change_menu_or_ingredient_stock_or_movement_history(): void
    {
        $this->actingAs($this->userWithRole('kasir'));
        $menu = $this->menu();
        $unit = Unit::create(['name' => 'Gram', 'abbreviation' => 'g']);
        $ingredient = app(IngredientStockService::class)->createWithOpeningBalance([
            'unit_id' => $unit->id,
            'name' => 'Beras',
            'minimum_stock' => '1.000',
            'average_cost_price' => '100.00',
            'is_active' => true,
        ], '10.000', 'pos-test:ingredient-opening');
        $menu->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '0.100']);
        $menuStock = $menu->stock;
        $ingredientStock = $ingredient->current_stock;
        $movements = $ingredient->stockMovements()->count();

        $this->post(route('kasir.pos.checkout'), ['checkout_token' => $this->checkoutToken(), 'customer_name' => 'John', 'items' => [['menu_id' => $menu->id, 'quantity' => 1]], 'cash_received' => 12000, 'payment_method' => 'cash', 'payment_timing' => 'now', 'order_type' => 'makan_di_tempat'])->assertRedirect();

        $this->assertSame($menuStock, $menu->refresh()->stock);
        $this->assertSame($ingredientStock, $ingredient->refresh()->current_stock);
        $this->assertSame($movements, $ingredient->stockMovements()->count());
    }

    public function test_cashier_cannot_open_another_cashiers_receipt(): void
    {
        $first = $this->userWithRole('kasir');
        $other = $this->userWithRole('kasir');
        $order = app(OrderService::class)->create([['menu_id' => $this->menu()->id, 'quantity' => 1]], $first->id, null, null, 'John', 'cash', true);

        $this->actingAs($other)->get(route('kasir.pos.receipt', $order))->assertNotFound();
    }

    public function test_cashier_history_search_is_paginated_and_limited_to_their_own_orders(): void
    {
        $cashier = $this->userWithRole('kasir');
        $otherCashier = $this->userWithRole('kasir');
        $menu = $this->menu('Soto Ayam', 14000);
        $ownOrder = app(OrderService::class)->create([['menu_id' => $menu->id, 'quantity' => 1]], $cashier->id, null, null, 'John', 'cash', true);
        $otherOrder = app(OrderService::class)->create([['menu_id' => $menu->id, 'quantity' => 2]], $otherCashier->id, null, null, 'Doe', 'cash', true);

        $this->actingAs($cashier)->get(route('kasir.pos.history', ['invoice' => substr($ownOrder->order_code, -5), 'date' => now()->format('Y-m-d')]))
            ->assertOk()->assertSee($ownOrder->order_code)->assertDontSee($otherOrder->order_code);
        $this->get(route('kasir.pos.history'))->assertOk()->assertSee($ownOrder->order_code)->assertDontSee($otherOrder->order_code);
        $this->actingAs($this->userWithRole('owner'))->get(route('kasir.pos.history'))->assertForbidden();
    }
}
