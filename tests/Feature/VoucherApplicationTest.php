<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        $role = Role::firstOrCreate(['name' => 'customer']);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function menu(int $price = 12000): Menu
    {
        return Menu::query()->create([
            'name' => 'Nasi Goreng',
            'category' => 'Makanan',
            'price' => $price,
            'stock' => 10,
            'is_available' => true,
        ]);
    }

    private function voucher(array $overrides = []): Voucher
    {
        return Voucher::query()->create(array_merge([
            'code' => 'HEMAT10',
            'name' => 'Hemat Sepuluh Persen',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'minimum_order' => 0,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'usage_limit' => null,
            'is_active' => true,
        ], $overrides));
    }

    private function customerOrderPayload(Menu $menu, string $voucherCode): array
    {
        return [
            'order_type' => 'bungkus',
            'payment_method' => 'qris',
            'pickup_date' => now()->toDateString(),
            'pickup_time' => '12:30',
            'voucher_code' => $voucherCode,
            'items' => [['id' => $menu->id, 'quantity' => 2]],
        ];
    }

    private function guestOrderPayload(Menu $menu, string $voucherCode): array
    {
        return [
            'customer_name' => 'Guest Baraya',
            'order_type' => 'bungkus',
            'payment_method' => 'tunai',
            'voucher_code' => $voucherCode,
            'items' => [['id' => $menu->id, 'quantity' => 1]],
        ];
    }

    public function test_registered_customer_can_apply_percentage_voucher_and_pay_discounted_total(): void
    {
        $voucher = $this->voucher();
        $menu = $this->menu();
        $customer = $this->customer();

        $this->actingAs($customer)
            ->postJson(route('customer.orders.store'), $this->customerOrderPayload($menu, ' hemat10 '))
            ->assertCreated()
            ->assertJsonPath('voucher_code', $voucher->code)
            ->assertJsonPath('discount_amount', '2400.00')
            ->assertJsonPath('total_amount', '21600.00');

        $order = Order::query()->sole();
        $this->assertSame($voucher->id, $order->voucher_id);
        $this->assertSame(2400, (int) $order->discount_amount);
        $this->assertSame(21600, (int) $order->total_amount);
        $this->assertSame(21600, (int) $order->payment->first()->amount);
    }

    public function test_guest_can_apply_fixed_voucher_and_discount_is_capped_at_order_subtotal(): void
    {
        $voucher = $this->voucher([
            'code' => 'POTONG50',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
        ]);
        $menu = $this->menu(12000);

        $this->postJson(route('guest.orders.store'), $this->guestOrderPayload($menu, $voucher->code))
            ->assertCreated()
            ->assertJsonPath('discount_amount', '12000.00')
            ->assertJsonPath('total_amount', '0.00');

        $order = Order::query()->sole();
        $this->assertSame($voucher->id, $order->voucher_id);
        $this->assertSame(0, (int) $order->payment->first()->amount);
    }

    public function test_voucher_minimum_date_and_active_rules_are_enforced_without_creating_orders(): void
    {
        $menu = $this->menu();
        $customer = $this->customer();
        $this->voucher(['code' => 'MINIMUM', 'minimum_order' => 50000]);
        $this->voucher(['code' => 'EXPIRED', 'ends_at' => now()->subDay()->toDateString()]);
        $this->voucher(['code' => 'FUTURE', 'starts_at' => now()->addDay()->toDateString()]);
        $this->voucher(['code' => 'INACTIVE', 'is_active' => false]);

        foreach (['MINIMUM', 'EXPIRED', 'FUTURE', 'INACTIVE', 'NOTREAL'] as $code) {
            $this->actingAs($customer)
                ->postJson(route('customer.orders.store'), $this->customerOrderPayload($menu, $code))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('voucher_code');
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_voucher_usage_limit_counts_successfully_created_orders_across_online_channels(): void
    {
        $this->voucher(['code' => 'ONEUSE', 'usage_limit' => 1]);
        $menu = $this->menu();

        $this->postJson(route('guest.orders.store'), $this->guestOrderPayload($menu, 'ONEUSE'))
            ->assertCreated();

        $this->actingAs($this->customer())
            ->postJson(route('customer.orders.store'), $this->customerOrderPayload($menu, 'ONEUSE'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('voucher_code');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
    }
}
