<?php

namespace Tests\Feature\Owner;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointTransaction;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Reward;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function menu(string $name = 'Es Teh Gratis', bool $available = true): Menu
    {
        return Menu::query()->create([
            'name' => $name,
            'category' => 'Minuman',
            'price' => 5000,
            'stock' => 10,
            'is_available' => $available,
        ]);
    }

    public function test_owner_can_view_and_create_a_free_menu_reward_with_required_points(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menu = $this->menu();

        $this->get(route('owner.rewards.index'))
            ->assertOk()
            ->assertSee('Tambah Reward Menu Gratis')
            ->assertSee('Belum ada reward menu gratis.');

        $this->post(route('owner.rewards.store'), [
            'menu_id' => $menu->id,
            'points_required' => 100,
            'is_active' => '1',
        ])->assertRedirect(route('owner.rewards.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('rewards', [
            'menu_id' => $menu->id,
            'points_required' => 100,
            'is_active' => true,
            'deleted_at' => null,
        ]);

        $this->get(route('owner.rewards.index'))
            ->assertOk()
            ->assertSee($menu->name)
            ->assertSee('100 poin');
    }

    public function test_owner_can_edit_reward_menu_point_cost_and_active_state(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $menu = $this->menu();
        $otherMenu = $this->menu('Pisang Goreng');
        $reward = Reward::query()->create([
            'menu_id' => $menu->id,
            'points_required' => 100,
            'is_active' => true,
        ]);

        $this->get(route('owner.rewards.edit', $reward))
            ->assertOk()
            ->assertSee($menu->name);

        $this->put(route('owner.rewards.update', $reward), [
            'menu_id' => $otherMenu->id,
            'points_required' => 250,
            'is_active' => '0',
        ])->assertRedirect(route('owner.rewards.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('rewards', [
            'id' => $reward->id,
            'menu_id' => $otherMenu->id,
            'points_required' => 250,
            'is_active' => 0,
        ]);
    }

    public function test_owner_can_remove_reward_without_erasing_redemption_ledger(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $customer = $this->userWithRole('customer');
        $account = LoyaltyAccount::query()->create(['user_id' => $customer->id, 'saldo_poin' => 0]);
        $reward = Reward::query()->create([
            'menu_id' => $this->menu()->id,
            'points_required' => 100,
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'customer_id' => $customer->id,
            'order_code' => 'RWD-HISTORY-1',
            'tracking_token' => 'reward-history-token',
            'customer_name' => $customer->name,
            'order_channel' => 'loyalty',
            'order_type' => 'bungkus',
            'order_status' => 'confirmed',
            'subtotal' => 5000,
            'discount_amount' => 5000,
            'total_amount' => 0,
        ]);
        $transaction = LoyaltyPointTransaction::query()->create([
            'loyalty_account_id' => $account->id,
            'order_id' => $order->id,
            'reward_id' => $reward->id,
            'type' => 'redeemed',
            'points' => 100,
            'description' => 'Penukaran Es Teh Gratis',
        ]);

        $this->delete(route('owner.rewards.destroy', $reward))
            ->assertRedirect(route('owner.rewards.index'))
            ->assertSessionHas('status');

        $this->assertSoftDeleted('rewards', ['id' => $reward->id]);
        $this->assertDatabaseHas('loyalty_point_transactions', [
            'id' => $transaction->id,
            'reward_id' => $reward->id,
            'points' => 100,
        ]);
        $this->assertSame($reward->id, $transaction->fresh()->reward->id);
        $this->get(route('owner.rewards.index'))
            ->assertSee('Belum ada reward menu gratis.')
            ->assertDontSee('100 poin');
    }

    public function test_inactive_menu_and_invalid_point_cost_are_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $unavailableMenu = $this->menu('Menu Tutup', false);

        $this->from(route('owner.rewards.index'))
            ->post(route('owner.rewards.store'), [
                'menu_id' => $unavailableMenu->id,
                'points_required' => 0,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['menu_id', 'points_required']);

        $this->assertDatabaseCount('rewards', 0);
    }

    public function test_only_owner_can_manage_rewards(): void
    {
        $reward = Reward::query()->create([
            'menu_id' => $this->menu()->id,
            'points_required' => 100,
            'is_active' => true,
        ]);
        $customer = $this->userWithRole('customer');

        $this->actingAs($customer)->get(route('owner.rewards.index'))->assertForbidden();
        $this->get(route('owner.rewards.edit', $reward))->assertForbidden();
        $this->post(route('owner.rewards.store'), [])->assertForbidden();
        $this->put(route('owner.rewards.update', $reward), [])->assertForbidden();
        $this->delete(route('owner.rewards.destroy', $reward))->assertForbidden();
    }
}
