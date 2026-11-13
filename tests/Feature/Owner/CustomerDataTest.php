<?php

namespace Tests\Feature\Owner;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CustomerDataTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName, string $email): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create([
            'email' => $email,
            'role_id' => $role->id,
        ]);
    }

    private function createOrder(?User $customer, string $orderCode, string $status = 'completed'): Order
    {
        return Order::query()->create([
            'customer_id' => $customer?->id,
            'order_code' => $orderCode,
            'tracking_token' => 'token-' . $orderCode,
            'customer_name' => $customer?->name ?? 'Pelanggan Guest',
            'customer_phone' => '08123456789',
            'order_channel' => 'online',
            'order_type' => 'bungkus',
            'order_status' => $status,
            'subtotal' => 25000,
            'total_amount' => 25000,
        ]);
    }

    public function test_owner_can_view_customer_accounts_and_existing_order_summary(): void
    {
        $this->actingAs($this->userWithRole('owner', 'owner@kantin.test'));
        $customer = $this->userWithRole('customer', 'customer@kantin.test');
        $legacyCustomer = $this->userWithRole('pelanggan', 'legacy@kantin.test');
        $this->userWithRole('cashier', 'cashier@kantin.test');
        $this->createOrder($customer, 'ORD-CUSTOMER-1');
        $this->createOrder($customer, 'ORD-CUSTOMER-2');
        $this->createOrder(null, 'ORD-GUEST-1');

        $this->get(route('owner.customers.index'))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee($customer->email)
            ->assertSee($legacyCustomer->email)
            ->assertSee('2')
            ->assertSee('Lihat Riwayat')
            ->assertDontSee('cashier@kantin.test')
            ->assertDontSee('ORD-GUEST-1');
    }

    public function test_owner_can_search_customers_by_name_or_email(): void
    {
        $this->actingAs($this->userWithRole('owner', 'owner@kantin.test'));
        $match = $this->userWithRole('customer', 'find-me@kantin.test');
        $other = $this->userWithRole('customer', 'other@kantin.test');

        $this->get(route('owner.customers.index', ['search' => 'find-me']))
            ->assertOk()
            ->assertSee($match->email)
            ->assertDontSee($other->email);
    }

    public function test_owner_can_view_customer_transaction_history_and_payment_information(): void
    {
        $this->actingAs($this->userWithRole('owner', 'owner@kantin.test'));
        $customer = $this->userWithRole('customer', 'history@kantin.test');
        $order = $this->createOrder($customer, 'ORD-HISTORY-1', 'pending');
        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'amount' => 25000,
            'paid_at' => now(),
        ]);
        $this->createOrder(null, 'ORD-UNLINKED-GUEST');

        $this->get(route('owner.customers.show', $customer))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee($customer->email)
            ->assertSee('ORD-HISTORY-1')
            ->assertSee('Menunggu Konfirmasi')
            ->assertSee('QRIS')
            ->assertSee('Sudah dibayar')
            ->assertSee('Rp25.000')
            ->assertDontSee('ORD-UNLINKED-GUEST');
    }

    public function test_customer_without_orders_has_an_empty_transaction_state(): void
    {
        $this->actingAs($this->userWithRole('owner', 'owner@kantin.test'));
        $customer = $this->userWithRole('customer', 'no-orders@kantin.test');

        $this->get(route('owner.customers.show', $customer))
            ->assertOk()
            ->assertSee($customer->email)
            ->assertSee('Pelanggan ini belum memiliki transaksi yang terhubung ke akunnya.');
    }

    public function test_customer_pages_are_owner_only_and_non_customer_profiles_are_not_found(): void
    {
        $owner = $this->userWithRole('owner', 'owner@kantin.test');
        $customer = $this->userWithRole('customer', 'customer@kantin.test');
        $cashier = $this->userWithRole('cashier', 'cashier@kantin.test');

        $this->actingAs($owner)
            ->get(route('owner.customers.show', $cashier))
            ->assertNotFound();

        $this->actingAs($cashier)
            ->get(route('owner.customers.index'))
            ->assertForbidden();
        $this->get(route('owner.customers.show', $customer))->assertForbidden();

        Auth::logout();
        $this->get(route('owner.customers.index'))->assertRedirect(route('login'));
    }
}
