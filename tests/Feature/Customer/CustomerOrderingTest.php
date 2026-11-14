<?php

namespace Tests\Feature\Customer;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OperationalHour;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        OperationalHour::query()->create([
            'day_of_week' => now()->dayOfWeek,
            'open_time' => '08:00',
            'close_time' => '17:00',
            'is_closed' => false,
        ]);
    }

    private function customer(): User
    {
        return User::factory()->create(['role_id' => Role::firstOrCreate(['name' => 'customer'])->id]);
    }

    public function test_customer_can_register_and_is_signed_in(): void
    {
        $this->get(route('register'))->assertOk();
        $this->post(route('register.store'), [
            'name' => 'Baraya Customer', 'email' => 'customer@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'customer@example.test']);
        $this->get(route('customer.dashboard'))->assertOk();
        $this->get(route('customer.menus.index'))->assertOk();
    }

    public function test_customer_checkout_uses_database_price_and_logged_in_identity(): void
    {
        $customer = $this->customer();
        $menu = Menu::create(['name' => 'Nasi', 'category' => 'Makanan', 'price' => 12000, 'stock' => 5, 'is_available' => true]);

        $response = $this->actingAs($customer)->postJson(route('customer.orders.store'), [
            'customer_name' => 'Nama Palsu', 'order_type' => 'bungkus', 'payment_method' => 'qris',
            'pickup_date' => now()->toDateString(), 'pickup_time' => '12:30',
            'items' => [['id' => $menu->id, 'quantity' => 2, 'price' => 1]],
        ])->assertCreated()->assertJsonPath('total_amount', '24000.00');

        $order = Order::with(['items', 'payment'])->firstOrFail();
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame($customer->name, $order->customer_name);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('bungkus', $order->order_type);
        $this->assertSame('qris', $order->payment->first()->payment_method);
        $this->assertSame(12000, $order->items->first()->unit_price);
        $this->assertSame(5, $menu->fresh()->stock);
    }

    public function test_customer_can_upload_proof_only_for_own_pending_order(): void
    {
        Storage::fake('public');
        $owner = $this->customer();
        $other = $this->customer();
        $menu = Menu::create(['name' => 'Nasi', 'category' => 'Makanan', 'price' => 12000, 'stock' => 5, 'is_available' => true]);
        $order = $this->actingAs($owner)->postJson(route('customer.orders.store'), [
            'order_type' => 'bungkus', 'payment_method' => 'qris', 'pickup_date' => now()->toDateString(),
            'pickup_time' => '12:30', 'items' => [['id' => $menu->id, 'quantity' => 1]],
        ])->assertCreated();
        $token = $order->json('tracking_token');

        $this->actingAs($other)->postJson(route('customer.orders.payment-proof', $token), [
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertNotFound();

        $this->actingAs($owner)->postJson(route('customer.orders.payment-proof', $token), [
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertOk()->assertJsonPath('message', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi kasir.');
        $this->assertNotNull(Order::first()->payment->first()->proof_image);
    }
}
