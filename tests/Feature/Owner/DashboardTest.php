<?php

namespace Tests\Feature\Owner;

use App\Models\Menu;
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
            ->assertSee('Data bahan baku belum tersedia.');
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
