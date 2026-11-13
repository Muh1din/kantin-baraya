<?php

namespace Tests\Feature;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointTransaction;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Reward;
use App\Models\Role;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLoyaltyTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function menu(string $name = 'Es Teh Manis', int $stock = 5): Menu
    {
        return Menu::query()->create([
            'name' => $name,
            'category' => 'Minuman',
            'price' => 5000,
            'stock' => $stock,
            'is_available' => true,
        ]);
    }

    public function test_points_are_credited_once_when_customer_payment_is_verified(): void
    {
        $customer = $this->userWithRole('customer');
        $cashier = $this->userWithRole('kasir');
        $order = Order::query()->create([
            'customer_id' => $customer->id,
            'order_code' => 'ORD-POINTS-25K',
            'tracking_token' => 'points-order-token',
            'customer_name' => $customer->name,
            'order_channel' => 'online',
            'order_type' => 'bungkus',
            'order_status' => 'pending',
            'subtotal' => 25000,
            'total_amount' => 25000,
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'amount' => 25000,
            'proof_image' => 'payment-proofs/proof.png',
        ]);

        $this->actingAs($cashier)
            ->post(route('kasir.orders.payments.verify', [$order, $payment]))
            ->assertRedirect(route('kasir.orders.show', $order));

        $account = LoyaltyAccount::query()->where('user_id', $customer->id)->firstOrFail();
        $this->assertSame(25, $account->saldo_poin);
        $this->assertDatabaseHas('loyalty_point_transactions', [
            'loyalty_account_id' => $account->id,
            'order_id' => $order->id,
            'type' => 'earned',
            'points' => 25,
        ]);
        $this->assertSame('loyalty_earned', $customer->notifications()->sole()->data['kind']);
        $this->assertStringContainsString('25 poin', $customer->notifications()->sole()->data['message']);

        $this->from(route('kasir.orders.show', $order))
            ->post(route('kasir.orders.payments.verify', [$order, $payment]))
            ->assertSessionHasErrors('payment');
        $this->assertSame(25, $account->fresh()->saldo_poin);
        $this->assertDatabaseCount('loyalty_point_transactions', 1);
    }

    public function test_guest_and_pos_transactions_do_not_mutate_customer_loyalty_accounts(): void
    {
        $customer = $this->userWithRole('customer');
        $account = LoyaltyAccount::query()->create(['user_id' => $customer->id, 'saldo_poin' => 7]);
        $menu = $this->menu();

        $this->postJson(route('guest.orders.store'), [
            'customer_name' => 'Guest',
            'order_type' => 'bungkus',
            'payment_method' => 'tunai',
            'items' => [['id' => $menu->id, 'quantity' => 1]],
        ])->assertCreated();

        $cashier = $this->userWithRole('kasir');
        app(OrderService::class)->create(
            [['menu_id' => $menu->id, 'quantity' => 1]],
            $cashier->id,
            null,
            5000,
            $customer->name,
        );

        $this->assertSame(7, $customer->fresh()->loyaltyAccount->saldo_poin);
        $this->assertSame(7, $account->fresh()->saldo_poin);
        $this->assertDatabaseCount('loyalty_point_transactions', 0);
    }

    public function test_customer_can_redeem_points_for_a_free_menu_order(): void
    {
        $customer = $this->userWithRole('customer');
        $account = LoyaltyAccount::query()->create(['user_id' => $customer->id, 'saldo_poin' => 250]);
        $menu = $this->menu();
        $reward = Reward::query()->create([
            'menu_id' => $menu->id,
            'points_required' => 200,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.loyalty.index'))
            ->assertOk()
            ->assertSee('250 poin')
            ->assertSee($menu->name)
            ->assertSee('200 poin')
            ->assertSee('Tukar Reward')
            ->assertSee('1 poin untuk setiap Rp1.000');

        $this->post(route('customer.loyalty.redeem', $reward))
            ->assertRedirect(route('customer.loyalty.index'))
            ->assertSessionHas('status');

        $this->get(route('customer.loyalty.index'))
            ->assertOk()
            ->assertSee('Riwayat Poin')
            ->assertSee('Penukaran ' . $menu->name)
            ->assertSee('−200 poin')
            ->assertSee('Pesanan RWD-');

        $order = Order::query()->sole();
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('loyalty', $order->order_channel);
        $this->assertSame('confirmed', $order->order_status);
        $this->assertSame(5000, (int) $order->subtotal);
        $this->assertSame(5000, (int) $order->discount_amount);
        $this->assertSame(0, (int) $order->total_amount);
        $this->assertSame(1, $order->items->count());
        $this->assertSame('loyalty', $order->payment->first()->payment_method);
        $this->assertSame(0, (int) $order->payment->first()->amount);
        $this->assertSame(50, $account->fresh()->saldo_poin);
        $this->assertSame(4, $menu->fresh()->stock);
        $this->assertDatabaseHas('loyalty_point_transactions', [
            'loyalty_account_id' => $account->id,
            'order_id' => $order->id,
            'reward_id' => $reward->id,
            'type' => 'redeemed',
            'points' => 200,
        ]);
        $this->assertSame('loyalty_redeemed', $customer->notifications()->sole()->data['kind']);
        $this->assertStringContainsString('200 poin', $customer->notifications()->sole()->data['message']);

        $chef = $this->userWithRole('koki');
        $this->actingAs($chef)
            ->get(route('chef.dashboard', ['status' => 'confirmed']))
            ->assertOk()
            ->assertSee($order->order_code);
    }

    public function test_insufficient_points_or_empty_stock_cannot_create_a_redemption(): void
    {
        $customer = $this->userWithRole('customer');
        $account = LoyaltyAccount::query()->create(['user_id' => $customer->id, 'saldo_poin' => 50]);
        $outOfStockMenu = $this->menu('Kue Habis', 0);
        $reward = Reward::query()->create([
            'menu_id' => $outOfStockMenu->id,
            'points_required' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->from(route('customer.loyalty.index'))
            ->post(route('customer.loyalty.redeem', $reward))
            ->assertSessionHasErrors('reward');

        $this->assertSame(50, $account->fresh()->saldo_poin);
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(0, LoyaltyPointTransaction::query()->count());
        $this->assertSame(0, $outOfStockMenu->fresh()->stock);
    }

    public function test_customer_loyalty_page_shows_earned_and_redeemed_point_history(): void
    {
        $customer = $this->userWithRole('customer');
        $account = LoyaltyAccount::query()->create(['user_id' => $customer->id, 'saldo_poin' => 35]);
        $menu = $this->menu();
        $reward = Reward::query()->create([
            'menu_id' => $menu->id,
            'points_required' => 15,
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'customer_id' => $customer->id,
            'order_code' => 'RWD-HISTORY-001',
            'tracking_token' => 'reward-history-customer-token',
            'customer_name' => $customer->name,
            'order_channel' => 'loyalty',
            'order_type' => 'bungkus',
            'order_status' => 'confirmed',
            'subtotal' => 5000,
            'discount_amount' => 5000,
            'total_amount' => 0,
        ]);
        LoyaltyPointTransaction::query()->create([
            'loyalty_account_id' => $account->id,
            'type' => 'earned',
            'points' => 50,
            'description' => 'Poin dari pembayaran pesanan',
        ]);
        LoyaltyPointTransaction::query()->create([
            'loyalty_account_id' => $account->id,
            'order_id' => $order->id,
            'reward_id' => $reward->id,
            'type' => 'redeemed',
            'points' => 15,
            'description' => 'Penukaran ' . $menu->name . ' dengan kode pesanan ' . $order->order_code,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.loyalty.index'))
            ->assertOk()
            ->assertSee('35 poin')
            ->assertSee('Poin dari pembayaran pesanan')
            ->assertSee('+50 poin')
            ->assertSee('Penukaran ' . $menu->name)
            ->assertSee('−15 poin')
            ->assertSee($order->order_code);
    }

    public function test_only_customers_can_view_and_redeem_loyalty_rewards(): void
    {
        $cashier = $this->userWithRole('kasir');
        $reward = Reward::query()->create([
            'menu_id' => $this->menu()->id,
            'points_required' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($cashier)
            ->get(route('customer.loyalty.index'))
            ->assertForbidden();
        $this->post(route('customer.loyalty.redeem', $reward))->assertForbidden();
    }
}
