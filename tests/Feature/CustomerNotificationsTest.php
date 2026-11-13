<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function order(User $customer): Order
    {
        return Order::query()->create([
            'customer_id' => $customer->id,
            'order_code' => 'ORD-NOTIFY-001',
            'tracking_token' => 'notification-order-token',
            'customer_name' => $customer->name,
            'order_channel' => 'online',
            'order_type' => 'bungkus',
            'order_status' => 'pending',
            'subtotal' => 5000,
            'total_amount' => 5000,
        ]);
    }

    public function test_customer_can_view_notifications_and_mark_one_as_read(): void
    {
        $customer = $this->userWithRole('customer');
        $customer->notify(new \App\Notifications\CustomerActivityNotification(
            'Status pesanan berubah', 'Pesanan ORD-NOTIFY-001 kini berstatus: Selesai.', ['kind' => 'order_status'],
        ));
        $notification = $customer->notifications()->sole();

        $this->actingAs($customer)
            ->get(route('customer.notifications.index'))
            ->assertOk()
            ->assertSee('Notifikasi')
            ->assertSee('Pesanan ORD-NOTIFY-001 kini berstatus: Selesai.')
            ->assertSee('Tandai sudah dibaca');

        $this->post(route('customer.notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
        $this->actingAs($customer)
            ->get(route('customer.notifications.index'))
            ->assertOk()
            ->assertDontSee('Tandai sudah dibaca');
    }

    public function test_order_status_changes_create_a_notification_for_its_customer(): void
    {
        $customer = $this->userWithRole('customer');
        $order = $this->order($customer);

        $order->update(['order_status' => 'confirmed']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $customer->id,
            'type' => \App\Notifications\CustomerActivityNotification::class,
        ]);
        $this->assertSame('order_status', $customer->notifications()->sole()->data['kind']);
        $this->assertStringContainsString('Dikonfirmasi', $customer->notifications()->sole()->data['message']);
    }

    public function test_customers_cannot_read_each_others_notifications(): void
    {
        $customer = $this->userWithRole('customer');
        $other = $this->userWithRole('customer');
        $other->notify(new \App\Notifications\CustomerActivityNotification('Poin bertambah', 'Anda mendapatkan poin.'));
        $notification = $other->notifications()->sole();

        $this->actingAs($customer)
            ->post(route('customer.notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
