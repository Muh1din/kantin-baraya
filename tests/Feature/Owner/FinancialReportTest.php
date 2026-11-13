<?php

namespace Tests\Feature\Owner;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function order(string $code): Order
    {
        return Order::query()->create([
            'order_code' => $code,
            'tracking_token' => 'report-' . $code,
            'customer_name' => 'Pelanggan',
            'order_channel' => 'online',
            'order_type' => 'bungkus',
            'order_status' => 'completed',
            'subtotal' => 10000,
            'total_amount' => 10000,
        ]);
    }

    private function payment(Order $order, string $method, string $status, int $amount, string $paidAt): Payment
    {
        return Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => $method,
            'payment_status' => $status,
            'amount' => $amount,
            'paid_at' => $paidAt,
        ]);
    }

    public function test_financial_report_aggregates_paid_payments_once_per_order_and_breaks_down_methods(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $firstOrder = $this->order('ORD-REPORT-1');
        $secondOrder = $this->order('ORD-REPORT-2');
        $unpaidOrder = $this->order('ORD-REPORT-3');
        $outsidePeriodOrder = $this->order('ORD-REPORT-4');

        $this->payment($firstOrder, 'qris', 'paid', 10000, '2026-11-02 10:00:00');
        $this->payment($firstOrder, 'tunai', 'paid', 10000, '2026-11-02 10:05:00');
        $this->payment($secondOrder, 'cash', 'paid', 15000, '2026-11-03 11:00:00');
        $this->payment($unpaidOrder, 'qris', 'pending', 9000, '2026-11-03 12:00:00');
        $this->payment($outsidePeriodOrder, 'qris', 'paid', 50000, '2026-10-31 23:59:59');

        $this->get(route('owner.reports.financial', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ]))
            ->assertOk()
            ->assertViewIs('owner.reports.financial')
            ->assertViewHas('totalRevenue', 25000)
            ->assertViewHas('transactionCount', 2)
            ->assertViewHas('paymentBreakdown', function ($rows): bool {
                return $rows->count() === 2
                    && (float) $rows->firstWhere('payment_method', 'tunai')?->total_amount === 10000.0
                    && (float) $rows->firstWhere('payment_method', 'cash')?->total_amount === 15000.0;
            })
            ->assertSee('Rp25.000')
            ->assertSee('TUNAI')
            ->assertSee('CASH')
            ->assertDontSee('QRIS');
    }

    public function test_owner_can_filter_report_by_date_and_failed_or_pending_payments_are_excluded(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $firstOrder = $this->order('ORD-REPORT-DATE-1');
        $secondOrder = $this->order('ORD-REPORT-DATE-2');
        $this->payment($firstOrder, 'qris', 'paid', 12500, '2026-11-15 23:59:59');
        $this->payment($secondOrder, 'tunai', 'failed', 40000, '2026-11-15 18:00:00');

        $this->get(route('owner.reports.financial', [
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-15',
        ]))
            ->assertOk()
            ->assertViewHas('totalRevenue', 12500)
            ->assertViewHas('transactionCount', 1)
            ->assertSee('2026-11-15');

        $this->from(route('owner.reports.financial'))
            ->get(route('owner.reports.financial', [
                'start_date' => '2026-11-16',
                'end_date' => '2026-11-15',
            ]))
            ->assertSessionHasErrors('end_date');
    }

    public function test_financial_report_is_restricted_to_owner(): void
    {
        $this->actingAs($this->userWithRole('kasir'))
            ->get(route('owner.reports.financial'))
            ->assertForbidden();

        Auth::logout();
        $this->get(route('owner.reports.financial'))->assertRedirect(route('login'));
    }
}
