<?php

namespace Tests\Feature\Cashier;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MenuSalesReportTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function menu(string $name): Menu
    {
        return Menu::query()->create([
            'name' => $name,
            'category' => 'Makanan',
            'price' => 10000,
            'stock' => 20,
            'is_available' => true,
        ]);
    }

    private function order(string $code, string $createdAt, string $channel = 'online'): Order
    {
        $order = Order::query()->create([
            'order_code' => $code,
            'tracking_token' => 'cashier-menu-sales-' . $code,
            'customer_name' => 'Pelanggan ' . $code,
            'order_channel' => $channel,
            'order_type' => 'bungkus',
            'order_status' => 'completed',
            'subtotal' => 20000,
            'total_amount' => $channel === 'loyalty' ? 0 : 20000,
        ]);
        $order->forceFill(['created_at' => $createdAt])->saveQuietly();

        return $order;
    }

    private function item(Order $order, Menu $menu, int $quantity): void
    {
        OrderItem::query()->create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'quantity' => $quantity,
            'unit_price' => 10000,
            'subtotal' => $quantity * 10000,
            'notes' => $order->order_channel === 'loyalty' ? 'Menu reward gratis.' : null,
        ]);
    }

    private function payment(Order $order, string $status, int $amount, ?string $paidAt): void
    {
        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'payment_status' => $status,
            'amount' => $amount,
            'paid_at' => $paidAt,
        ]);
    }

    public function test_cashier_report_summarizes_paid_menu_sales_once_and_excludes_free_rewards(): void
    {
        $cashier = $this->userWithRole('kasir');
        $rice = $this->menu('Nasi Goreng');
        $tea = $this->menu('Es Teh');
        $paidOrder = $this->order('ORD-MENU-PAID', '2026-11-12 10:00:00');
        $this->item($paidOrder, $rice, 2);
        $this->item($paidOrder, $tea, 1);
        $this->payment($paidOrder, 'paid', 25000, '2026-11-12 10:05:00');
        $this->payment($paidOrder, 'paid', 25000, '2026-11-12 10:06:00');

        $pendingOrder = $this->order('ORD-MENU-PENDING', '2026-11-12 11:00:00');
        $this->item($pendingOrder, $rice, 7);
        $this->payment($pendingOrder, 'pending', 70000, null);

        $freeRewardOrder = $this->order('ORD-MENU-REWARD', '2026-11-12 11:30:00', 'loyalty');
        $this->item($freeRewardOrder, $rice, 3);
        $this->payment($freeRewardOrder, 'paid', 0, '2026-11-12 11:30:00');

        $outsideOrder = $this->order('ORD-MENU-OUTSIDE', '2026-10-31 11:00:00');
        $this->item($outsideOrder, $rice, 5);
        $this->payment($outsideOrder, 'paid', 50000, '2026-10-31 11:05:00');

        $this->actingAs($cashier)
            ->get(route('kasir.reports.menu-sales', ['start_date' => '2026-11-01', 'end_date' => '2026-11-30']))
            ->assertOk()
            ->assertViewIs('cashier.reports.menu-sales')
            ->assertViewHas('menuSales', function ($rows): bool {
                return $rows->total() === 2
                    && (int) $rows->firstWhere('menu_name', 'Nasi Goreng')?->quantity_sold === 2
                    && (float) $rows->firstWhere('menu_name', 'Nasi Goreng')?->revenue === 20000.0
                    && (int) $rows->firstWhere('menu_name', 'Es Teh')?->quantity_sold === 1
                    && (float) $rows->firstWhere('menu_name', 'Es Teh')?->revenue === 10000.0;
            })
            ->assertViewHas('quantityTotal', 3)
            ->assertViewHas('revenueTotal', 30000.0)
            ->assertSee('Nasi Goreng')
            ->assertSee('Es Teh')
            ->assertSee('3 porsi')
            ->assertSee('Rp30.000')
            ->assertDontSee('ORD-MENU-PENDING')
            ->assertDontSee('ORD-MENU-REWARD')
            ->assertDontSee('ORD-MENU-OUTSIDE');
    }

    public function test_cashier_menu_sales_report_filters_payment_period_and_rejects_reversed_dates(): void
    {
        $cashier = $this->userWithRole('kasir');
        $menu = $this->menu('Soto Ayam');
        $insideOrder = $this->order('ORD-MENU-INSIDE', '2026-11-14 12:00:00');
        $this->item($insideOrder, $menu, 3);
        $this->payment($insideOrder, 'paid', 30000, '2026-11-15 23:59:59');
        $outsideOrder = $this->order('ORD-MENU-OUTSIDE-PERIOD', '2026-11-15 12:00:00');
        $this->item($outsideOrder, $menu, 9);
        $this->payment($outsideOrder, 'paid', 90000, '2026-11-16 00:00:00');

        $this->actingAs($cashier)
            ->get(route('kasir.reports.menu-sales', ['start_date' => '2026-11-15', 'end_date' => '2026-11-15']))
            ->assertOk()
            ->assertViewHas('quantityTotal', 3)
            ->assertViewHas('revenueTotal', 30000.0)
            ->assertSee('3 porsi');

        $this->from(route('kasir.reports.menu-sales'))
            ->get(route('kasir.reports.menu-sales', ['start_date' => '2026-11-20', 'end_date' => '2026-11-19']))
            ->assertSessionHasErrors('end_date');
    }

    public function test_menu_sales_report_is_read_only_and_restricted_to_cashiers(): void
    {
        $customer = $this->userWithRole('customer');
        $this->actingAs($customer)->get(route('kasir.reports.menu-sales'))->assertForbidden();

        $cashier = $this->userWithRole('kasir');
        $this->actingAs($cashier)->post('/kasir/reports/menu-sales')->assertStatus(405);

        Auth::logout();
        $this->get(route('kasir.reports.menu-sales'))->assertRedirect(route('login'));
    }
}
