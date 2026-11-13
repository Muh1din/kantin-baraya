<?php

namespace Tests\Feature\Cashier;

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

    private function order(string $code, string $status, string $createdAt, string $channel = 'online', ?int $createdBy = null): Order
    {
        $order = Order::query()->create([
            'order_code' => $code,
            'tracking_token' => 'transaction-report-' . $code,
            'customer_name' => 'Pelanggan ' . $code,
            'customer_phone' => '08123456789',
            'order_channel' => $channel,
            'order_type' => 'bungkus',
            'order_status' => $status,
            'subtotal' => 25000,
            'total_amount' => 25000,
            'created_by' => $createdBy,
        ]);
        $order->forceFill(['created_at' => $createdAt])->saveQuietly();

        return $order;
    }

    private function payment(Order $order, string $method, string $status, int $amount): Payment
    {
        return Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => $method,
            'payment_status' => $status,
            'amount' => $amount,
            'paid_at' => $status === 'paid' ? $order->created_at : null,
        ]);
    }

    public function test_cashier_can_see_one_row_per_valid_order_with_payment_details(): void
    {
        $cashier = $this->userWithRole('kasir');
        $order = $this->order('ORD-REPORT-1', 'completed', '2026-11-12 10:30:00');
        $this->payment($order, 'qris', 'paid', 22000);
        $this->payment($order, 'qris', 'paid', 22000);
        $unpaidLedger = $this->order('ORD-REPORT-NO-PAYMENT', 'completed', '2026-11-12 11:30:00');
        $this->order('ORD-REPORT-OUTSIDE', 'completed', '2026-10-30 10:00:00');
        $this->payment($unpaidLedger, 'tunai', 'pending', 18000);

        $this->actingAs($cashier)
            ->get(route('kasir.reports.transactions', ['start_date' => '2026-11-01', 'end_date' => '2026-11-30']))
            ->assertOk()
            ->assertViewIs('cashier.reports.transactions')
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 2)
            ->assertSee('ORD-REPORT-1', false)
            ->assertSee('Pelanggan ORD-REPORT-1')
            ->assertSee('12/11/2026 10:30')
            ->assertSee('Rp22.000')
            ->assertSee('QRIS')
            ->assertSee('Berhasil')
            ->assertSee('Selesai')
            ->assertSee('ORD-REPORT-NO-PAYMENT')
            ->assertDontSee('ORD-REPORT-OUTSIDE')
            ->assertSee('Lihat');
    }

    public function test_cashier_can_filter_report_by_order_date_and_status(): void
    {
        $cashier = $this->userWithRole('kasir');
        $included = $this->order('ORD-INCLUDED', 'completed', '2026-11-10 12:00:00');
        $outsideDate = $this->order('ORD-OUTSIDE-DATE', 'completed', '2026-10-30 12:00:00');
        $otherStatus = $this->order('ORD-OTHER-STATUS', 'pending', '2026-11-10 13:00:00');
        $this->payment($included, 'qris', 'paid', 10000);
        $this->payment($outsideDate, 'cash', 'paid', 20000);
        $this->payment($otherStatus, 'tunai', 'pending', 15000);

        $this->actingAs($cashier)
            ->get(route('kasir.reports.transactions', [
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-10',
                'status' => 'completed',
            ]))
            ->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 1)
            ->assertSee('ORD-INCLUDED')
            ->assertDontSee('ORD-OUTSIDE-DATE')
            ->assertDontSee('ORD-OTHER-STATUS');

        $this->from(route('kasir.reports.transactions'))
            ->get(route('kasir.reports.transactions', ['start_date' => '2026-11-11', 'end_date' => '2026-11-10']))
            ->assertSessionHasErrors('end_date');
    }

    public function test_only_cashiers_can_view_transaction_report_and_it_has_no_write_routes(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->get(route('kasir.reports.transactions'))
            ->assertForbidden();

        $cashier = $this->userWithRole('kasir');
        $order = $this->order('ORD-READONLY', 'completed', '2026-11-13 09:00:00');
        $this->payment($order, 'tunai', 'paid', 25000);
        $this->actingAs($cashier)->post('/kasir/reports/transactions')->assertStatus(405);
    }
}
