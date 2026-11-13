<?php

namespace Tests\Feature\Owner;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TransactionReportTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function order(string $code, string $status, string $customerName, string $createdAt): Order
    {
        $order = Order::query()->create([
            'order_code' => $code,
            'tracking_token' => 'transaction-report-' . $code,
            'customer_name' => $customerName,
            'customer_phone' => '08123456789',
            'order_channel' => 'online',
            'order_type' => 'bungkus',
            'order_status' => $status,
            'subtotal' => 25000,
            'total_amount' => 25000,
        ]);
        $order->forceFill(['created_at' => $createdAt])->saveQuietly();

        return $order;
    }

    private function payment(Order $order, string $method, string $status, int $amount): void
    {
        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => $method,
            'payment_status' => $status,
            'amount' => $amount,
            'paid_at' => $status === 'paid' ? $order->created_at : null,
        ]);
    }

    public function test_owner_can_view_transaction_rows_with_payment_and_status_details(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $completed = $this->order('ORD-TX-1', 'completed', 'Pelanggan Satu', '2026-11-12 10:30:00');
        $pending = $this->order('ORD-TX-2', 'pending', 'Pelanggan Dua', '2026-11-11 09:15:00');
        $this->payment($completed, 'qris', 'paid', 22000);
        $this->payment($pending, 'tunai', 'pending', 18000);

        $this->get(route('owner.reports.transactions', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ]))
            ->assertOk()
            ->assertViewIs('owner.reports.transactions')
            ->assertViewHas('orders', fn($orders) => $orders->total() === 2)
            ->assertSee('ORD-TX-1')
            ->assertSee('Pelanggan Satu')
            ->assertSee('12/11/2026 10:30')
            ->assertSee('Rp22.000')
            ->assertSee('QRIS')
            ->assertSee('Berhasil')
            ->assertSee('Selesai')
            ->assertSee('ORD-TX-2')
            ->assertSee('Rp18.000')
            ->assertSee('TUNAI')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Menunggu Konfirmasi');
    }

    public function test_transaction_report_filters_by_date_range_and_order_status(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $included = $this->order('ORD-INCLUDED', 'completed', 'Pelanggan Cocok', '2026-11-10 12:00:00');
        $outsideDate = $this->order('ORD-OUTSIDE-DATE', 'completed', 'Pelanggan Tanggal', '2026-10-30 12:00:00');
        $otherStatus = $this->order('ORD-OTHER-STATUS', 'pending', 'Pelanggan Status', '2026-11-10 13:00:00');
        $this->payment($included, 'qris', 'paid', 10000);
        $this->payment($outsideDate, 'cash', 'paid', 20000);
        $this->payment($otherStatus, 'tunai', 'pending', 15000);

        $this->get(route('owner.reports.transactions', [
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-10',
            'status' => 'completed',
        ]))
            ->assertOk()
            ->assertViewHas('orders', fn($orders) => $orders->total() === 1)
            ->assertSee('ORD-INCLUDED')
            ->assertDontSee('ORD-OUTSIDE-DATE')
            ->assertDontSee('ORD-OTHER-STATUS');

        $this->from(route('owner.reports.transactions'))
            ->get(route('owner.reports.transactions', [
                'start_date' => '2026-11-11',
                'end_date' => '2026-11-10',
            ]))
            ->assertSessionHasErrors('end_date');
    }

    public function test_owner_transaction_report_is_forbidden_to_other_roles(): void
    {
        $this->actingAs($this->userWithRole('kasir'))
            ->get(route('owner.reports.transactions'))
            ->assertForbidden();

        Auth::logout();
        $this->get(route('owner.reports.transactions'))->assertRedirect(route('login'));
    }
}
