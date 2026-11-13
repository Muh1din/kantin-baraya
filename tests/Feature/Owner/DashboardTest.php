<?php

namespace Tests\Feature\Owner;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createMenu(string $name, bool $available): void
    {
        Menu::create([
            'name' => $name,
            'category' => 'makanan',
            'price' => 10000,
            'stock' => 5,
            'is_available' => $available,
        ]);
    }

    public function test_owner_can_open_dashboard_and_sees_real_active_menu_count_and_routes(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $this->createMenu('Menu Aktif', true);
        $this->createMenu('Menu Nonaktif', false);

        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Owner')
            ->assertSee('Jumlah menu aktif')
            ->assertSee('>1</p>', false)
            ->assertSee(route('owner.menu-categories.index'), false)
            ->assertSee(route('owner.menus.index'), false)
            ->assertSee('Bahan Baku')
            ->assertSee('Belum tersedia')
            ->assertSee('POS');
    }

    public function test_owner_dashboard_handles_empty_menu_data_and_marks_unavailable_metrics(): void
    {
        $this->actingAs($this->userWithRole('owner'))
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Jumlah menu aktif')
            ->assertSee('>0</p>', false)
            ->assertSee('Penjualan hari ini')
            ->assertSee('Pesanan aktif')
            ->assertSee('Stok bahan baku rendah')
            ->assertSee('Data penjualan belum tersedia.')
            ->assertSee('Data pesanan belum tersedia.')
            ->assertSee('Belum ada pesanan.')
            ->assertSee('Pesanan terbaru akan muncul di sini setelah pelanggan melakukan pemesanan.')
            ->assertSee('Data bahan baku belum tersedia.');
    }

    public function test_owner_dashboard_shows_only_five_latest_orders_with_read_only_status_labels(): void
    {
        $this->actingAs($this->userWithRole('owner'));

        $statuses = ['pending', 'confirmed', 'rejected', 'in_progress', 'completed', 'pending'];
        foreach ($statuses as $index => $status) {
            $order = Order::create([
                'order_code' => 'ORD-OWNER-' . ($index + 1),
                'tracking_token' => 'owner-dashboard-token-' . ($index + 1),
                'customer_name' => 'Pelanggan ' . ($index + 1),
                'order_channel' => 'online',
                'order_type' => 'bungkus',
                'order_status' => $status,
                'subtotal' => 10000,
                'total_amount' => 10000,
            ]);
            $order->forceFill([
                'created_at' => now()->subMinutes($index),
            ])->saveQuietly();
        }

        $response = $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertViewIs('owner.dashboard')
            ->assertViewHas('recentOrders', fn ($orders) => $orders->count() === 5);

        foreach ([
            'ORD-OWNER-1',
            'ORD-OWNER-2',
            'ORD-OWNER-3',
            'ORD-OWNER-4',
            'ORD-OWNER-5',
            'Menunggu Konfirmasi',
            'Dikonfirmasi',
            'Ditolak',
            'Sedang Dimasak',
            'Selesai',
            'Rp10.000',
        ] as $content) {
            $response->assertSee($content);
        }

        $response->assertDontSee('ORD-OWNER-6');
        $this->assertSame(5, substr_count($response->getContent(), 'ORD-OWNER-'));
        $positions = array_map(
            fn (int $number): int|false => strpos($response->getContent(), 'ORD-OWNER-' . $number),
            range(1, 5),
        );
        $sortedPositions = $positions;
        sort($sortedPositions);
        $this->assertSame($sortedPositions, $positions);
        $response->assertDontSee('Konfirmasi Pesanan')
            ->assertDontSee('Tolak Pesanan');
    }

    public function test_owner_dashboard_shows_an_informative_empty_state_when_there_are_no_orders(): void
    {
        $this->actingAs($this->userWithRole('owner'))
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Aktivitas Pesanan Terbaru')
            ->assertSee('Belum ada pesanan.')
            ->assertSee('Pesanan terbaru akan muncul di sini setelah pelanggan melakukan pemesanan.');
    }

    public function test_other_roles_cannot_open_owner_dashboard_and_their_dashboards_still_work(): void
    {
        $dashboards = [
            'kasir' => ['route' => 'kasir.dashboard', 'label' => 'Kasir'],
            'koki' => ['route' => 'koki.dashboard', 'label' => 'Koki'],
            'pelanggan' => ['route' => 'pelanggan.dashboard', 'label' => 'Pelanggan'],
        ];

        foreach ($dashboards as $role => $dashboard) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('owner.dashboard'))
                ->assertForbidden();

            $this->get(route($dashboard['route']))
                ->assertOk()
                ->assertSee('Dashboard ' . $dashboard['label']);
        }
    }
}
