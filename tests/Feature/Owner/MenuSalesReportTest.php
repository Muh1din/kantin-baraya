<?php

namespace Tests\Feature\Owner;

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

    private function order(string $code, string $createdAt): Order
    {
        $order = Order::query()->create([
            'order_code' => $code,
            'tracking_token' => 'menu-sales-' . $code,
            'customer_name' => 'Pelanggan',
            'order_channel' => 'online',
            'order_type' => 'bungkus',
            'order_status' => 'completed',
            'subtotal' => 10000,
            'total_amount' => 10000,
        ]);
        $order->forceFill(['created_at' => $createdAt])->saveQuietly();

        return $order;
    }

    private function item(Order $order, Menu $menu, int $quantity, int $unitPrice): void
    {
        OrderItem::query()->create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ]);
    }

    private function payment(Order $order, string $status, string $paidAt): void
    {
        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'payment_status' => $status,
            'amount' => 20000,
            'paid_at' => $status === 'paid' ? $paidAt : null,
        ]);
    }

    public function test_owner_sees_menu_quantity_and_revenue_from_valid_paid_orders_once(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $rice = $this->menu('Nasi Goreng');
        $tea = $this->menu('Es Teh');
        $paidOrder = $this->order('ORD-MENU-PAID', '2026-11-12 10:00:00');
        $pendingOrder = $this->order('ORD-MENU-PENDING', '2026-11-12 11:00:00');
        $freeRewardOrder = $this->order('ORD-MENU-REWARD', '2026-11-12 11:30:00');
        $outsideOrder = $this->order('ORD-MENU-OUTSIDE', '2026-10-31 11:00:00');
        $freeRewardOrder->update(['order_channel' => 'loyalty']);

        $this->item($paidOrder, $rice, 2, 10000);
        $this->item($paidOrder, $tea, 1, 5000);
        $this->item($pendingOrder, $rice, 7, 10000);
        $this->item($freeRewardOrder, $rice, 3, 10000);
        $this->item($outsideOrder, $rice, 5, 10000);

        $this->payment($paidOrder, 'paid', '2026-11-12 10:05:00');
        $this->payment($paidOrder, 'paid', '2026-11-12 10:06:00');
        $this->payment($pendingOrder, 'pending', '2026-11-12 11:00:00');
        $this->payment($freeRewardOrder, 'paid', '2026-11-12 11:30:00');
        Payment::query()->where('order_id', $freeRewardOrder->id)->update(['amount' => 0]);
        $this->payment($outsideOrder, 'paid', '2026-10-31 11:05:00');

        $this->get(route('owner.reports.menu-sales', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ]))
            ->assertOk()
            ->assertViewIs('owner.reports.menu-sales')
            ->assertViewHas('menuSales', function ($rows): bool {
                return $rows->total() === 2
                    && (int) $rows->firstWhere('menu_name', 'Nasi Goreng')?->quantity_sold === 2
                    && (float) $rows->firstWhere('menu_name', 'Nasi Goreng')?->revenue === 20000.0
                    && (int) $rows->firstWhere('menu_name', 'Es Teh')?->quantity_sold === 1
                    && (float) $rows->firstWhere('menu_name', 'Es Teh')?->revenue === 5000.0;
            })
            ->assertSee('Nasi Goreng')
            ->assertSee('Es Teh')
            ->assertSee('Rp20.000')
            ->assertSee('Rp5.000')
            ->assertDontSee('ORD-MENU-PENDING');
    }

    public function test_menu_sales_report_filters_period_and_rejects_reversed_dates(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menu = $this->menu('Soto Ayam');
        $insideOrder = $this->order('ORD-MENU-INSIDE', '2026-11-15 12:00:00');
        $outsideOrder = $this->order('ORD-MENU-OUTSIDE-FILTER', '2026-11-16 12:00:00');
        $this->item($insideOrder, $menu, 3, 10000);
        $this->item($outsideOrder, $menu, 9, 10000);
        $this->payment($insideOrder, 'paid', '2026-11-15 12:01:00');
        $this->payment($outsideOrder, 'paid', '2026-11-16 12:01:00');

        $this->get(route('owner.reports.menu-sales', [
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-15',
        ]))
            ->assertOk()
            ->assertViewHas('menuSales', fn($rows) => $rows->total() === 1
                && (int) $rows->first()?->quantity_sold === 3)
            ->assertSee('3 porsi');

        $this->from(route('owner.reports.menu-sales'))
            ->get(route('owner.reports.menu-sales', [
                'start_date' => '2026-11-20',
                'end_date' => '2026-11-19',
            ]))
            ->assertSessionHasErrors('end_date');
    }

    public function test_menu_sales_report_is_restricted_to_owner(): void
    {
        $this->actingAs($this->userWithRole('kasir'))
            ->get(route('owner.reports.menu-sales'))
            ->assertForbidden();

        Auth::logout();
        $this->get(route('owner.reports.menu-sales'))->assertRedirect(route('login'));
    }
}
