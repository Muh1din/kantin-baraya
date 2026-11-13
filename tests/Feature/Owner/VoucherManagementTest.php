<?php

namespace Tests\Feature\Owner;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function voucherData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'baraya10',
            'name' => 'Diskon Baraya',
            'discount_type' => 'percentage',
            'discount_value' => '10',
            'minimum_order' => '20000',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'usage_limit' => '25',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_owner_can_view_and_create_a_voucher(): void
    {
        $this->actingAs($this->userWithRole('owner'))
            ->get(route('owner.vouchers.index'))
            ->assertOk()
            ->assertSee('Buat Voucher')
            ->assertSee('Belum ada voucher diskon.');

        $this->post(route('owner.vouchers.store'), $this->voucherData())
            ->assertRedirect(route('owner.vouchers.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('vouchers', [
            'code' => 'BARAYA10',
            'name' => 'Diskon Baraya',
            'discount_type' => 'percentage',
            'discount_value' => '10.00',
            'minimum_order' => '20000.00',
            'usage_limit' => 25,
            'is_active' => true,
        ]);

        $this->get(route('owner.vouchers.index'))
            ->assertOk()
            ->assertSee('BARAYA10')
            ->assertSee('Diskon Baraya')
            ->assertSee('10%');
    }

    public function test_owner_can_edit_a_voucher(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $voucher = Voucher::query()->create([
            'code' => 'LAMA',
            'name' => 'Voucher Lama',
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'minimum_order' => 0,
            'is_active' => true,
        ]);

        $this->get(route('owner.vouchers.edit', $voucher))
            ->assertOk()
            ->assertSee('Voucher Lama');

        $this->put(route('owner.vouchers.update', $voucher), $this->voucherData([
            'code' => 'baru20',
            'discount_type' => 'percentage',
            'discount_value' => '20',
            'minimum_order' => '50000',
            'usage_limit' => '',
        ]))->assertRedirect(route('owner.vouchers.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'code' => 'BARU20',
            'discount_type' => 'percentage',
            'discount_value' => '20.00',
            'minimum_order' => '50000.00',
            'usage_limit' => null,
        ]);
    }

    public function test_owner_can_delete_a_voucher_without_erasing_order_discount_history(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $voucher = Voucher::query()->create([
            'code' => 'USED10',
            'name' => 'Voucher Terpakai',
            'discount_type' => 'fixed',
            'discount_value' => 1000,
            'minimum_order' => 0,
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'voucher_id' => $voucher->id,
            'order_code' => 'ORD-VOUCHER-HISTORY',
            'tracking_token' => 'voucher-history-token',
            'customer_name' => 'Pelanggan',
            'order_channel' => 'online',
            'order_type' => 'bungkus',
            'order_status' => 'pending',
            'subtotal' => 10000,
            'discount_amount' => 1000,
            'total_amount' => 9000,
        ]);

        $this->delete(route('owner.vouchers.destroy', $voucher))
            ->assertRedirect(route('owner.vouchers.index'))
            ->assertSessionHas('status');

        $this->assertSoftDeleted('vouchers', ['id' => $voucher->id]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'voucher_id' => $voucher->id,
            'discount_amount' => '1000.00',
            'total_amount' => '9000.00',
        ]);
        $this->assertSame('USED10', $order->fresh()->voucher->code);
        $this->get(route('owner.vouchers.index'))->assertDontSee('USED10');
    }

    public function test_invalid_or_duplicate_voucher_rules_are_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        Voucher::query()->create([
            'code' => 'BARAYA10',
            'name' => 'Voucher Existing',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'minimum_order' => 0,
            'is_active' => true,
        ]);

        $this->from(route('owner.vouchers.index'))
            ->post(route('owner.vouchers.store'), $this->voucherData([
                'code' => 'baraya10',
            ]))
            ->assertSessionHasErrors('code');

        $this->from(route('owner.vouchers.index'))
            ->post(route('owner.vouchers.store'), $this->voucherData([
                'code' => 'OVER100',
                'discount_value' => '101',
            ]))
            ->assertSessionHasErrors('discount_value');

        $this->from(route('owner.vouchers.index'))
            ->post(route('owner.vouchers.store'), $this->voucherData([
                'code' => 'BADDATE',
                'starts_at' => now()->addDays(2)->toDateString(),
                'ends_at' => now()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('ends_at');

        $this->assertDatabaseCount('vouchers', 1);
    }

    public function test_only_owner_can_access_voucher_management_routes(): void
    {
        $voucher = Voucher::query()->create([
            'code' => 'OWNERONLY',
            'name' => 'Voucher Owner',
            'discount_type' => 'fixed',
            'discount_value' => 1000,
            'minimum_order' => 0,
            'is_active' => true,
        ]);
        $cashier = $this->userWithRole('cashier');

        $this->actingAs($cashier)->get(route('owner.vouchers.index'))->assertForbidden();
        $this->get(route('owner.vouchers.edit', $voucher))->assertForbidden();
        $this->post(route('owner.vouchers.store'), $this->voucherData())->assertForbidden();
        $this->put(route('owner.vouchers.update', $voucher), $this->voucherData())->assertForbidden();
        $this->delete(route('owner.vouchers.destroy', $voucher))->assertForbidden();
    }
}
