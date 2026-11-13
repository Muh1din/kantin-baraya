<?php

namespace Tests\Feature\Cashier;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDataTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName, string $name, string $email): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['name' => $name, 'email' => $email, 'role_id' => $role->id]);
    }

    private function createOrder(User $customer, string $code, string $phone = '08123456789'): Order
    {
        return Order::query()->create([
            'customer_id' => $customer->id,
            'order_code' => $code,
            'tracking_token' => 'token-' . $code,
            'customer_name' => $customer->name,
            'customer_phone' => $phone,
            'order_channel' => 'online',
            'order_type' => 'bungkus',
            'order_status' => 'completed',
            'subtotal' => 25000,
            'total_amount' => 25000,
        ]);
    }

    public function test_cashier_can_view_registered_customers_and_latest_available_phone(): void
    {
        $cashier = $this->userWithRole('kasir', 'Kasir', 'cashier@example.test');
        $customer = $this->userWithRole('customer', 'Siti Pelanggan', 'siti@example.test');
        $this->createOrder($customer, 'ORD-CUSTOMER-1', '08111111111');
        $this->createOrder($customer, 'ORD-CUSTOMER-2', '08222222222');
        $this->userWithRole('customer', 'Tanpa Transaksi', 'empty@example.test');

        $this->actingAs($cashier)
            ->get(route('kasir.customers.index'))
            ->assertOk()
            ->assertSee('Siti Pelanggan')
            ->assertSee('siti@example.test')
            ->assertSee('08222222222')
            ->assertSee('2')
            ->assertSee('Lihat Riwayat')
            ->assertSee('Tanpa Transaksi')
            ->assertSee('Tidak tersedia');
    }

    public function test_cashier_can_search_customers_by_name_or_email(): void
    {
        $cashier = $this->userWithRole('kasir', 'Kasir', 'cashier@example.test');
        $match = $this->userWithRole('customer', 'Nama Target', 'target@example.test');
        $other = $this->userWithRole('customer', 'Pelanggan Lain', 'other@example.test');

        $this->actingAs($cashier)
            ->get(route('kasir.customers.index', ['search' => 'Nama Target']))
            ->assertOk()->assertSee($match->email)->assertDontSee($other->email);

        $this->get(route('kasir.customers.index', ['search' => 'target@example.test']))
            ->assertOk()->assertSee($match->name)->assertDontSee($other->name);
    }

    public function test_cashier_can_view_customer_transaction_history_and_contact_snapshot(): void
    {
        $cashier = $this->userWithRole('kasir', 'Kasir', 'cashier@example.test');
        $customer = $this->userWithRole('customer', 'Riwayat Customer', 'history@example.test');
        $order = $this->createOrder($customer, 'ORD-CASHIER-HISTORY');
        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'amount' => 25000,
            'paid_at' => now(),
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.customers.show', $customer))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee($customer->email)
            ->assertSee('08123456789')
            ->assertSee('ORD-CASHIER-HISTORY')
            ->assertSee('Selesai')
            ->assertSee('QRIS')
            ->assertSee('Sudah dibayar')
            ->assertSee('Rp25.000');

        $this->post('/kasir/customers/' . $customer->id)->assertStatus(405);
        $this->delete('/kasir/customers/' . $customer->id)->assertStatus(405);
    }

    public function test_only_cashiers_can_view_customer_directory(): void
    {
        $customer = $this->userWithRole('customer', 'Customer', 'customer@example.test');
        $this->actingAs($customer)->get(route('kasir.customers.index'))->assertForbidden();
        $this->get(route('kasir.customers.show', $customer))->assertForbidden();
    }
}
