<?php

namespace Tests\Feature\Owner;

use App\Models\MenuCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_owner_can_view_menu_categories(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $category = MenuCategory::create(['name' => 'Makanan Berat']);

        $this->get(route('owner.menu-categories.index'))
            ->assertOk()
            ->assertSee('Makanan Berat')
            ->assertSee('Tambah kategori');
    }

    public function test_owner_can_add_a_menu_category(): void
    {
        $this->actingAs($this->userWithRole('owner'))
            ->post(route('owner.menu-categories.store'), [
                'name' => 'Minuman',
                'description' => 'Minuman dingin dan hangat',
            ])
            ->assertRedirect(route('owner.menu-categories.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('menu_categories', [
            'name' => 'Minuman',
            'description' => 'Minuman dingin dan hangat',
            'is_active' => true,
        ]);
    }

    public function test_owner_can_edit_a_menu_category(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $category = MenuCategory::create(['name' => 'Makanan']);

        $this->put(route('owner.menu-categories.update', $category), [
            'name' => 'Makanan Berat',
            'description' => 'Menu utama',
        ])->assertRedirect(route('owner.menu-categories.index'));

        $this->assertDatabaseHas('menu_categories', [
            'id' => $category->id,
            'name' => 'Makanan Berat',
            'description' => 'Menu utama',
        ]);
    }

    public function test_owner_can_toggle_category_status_without_deleting_it(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $category = MenuCategory::create(['name' => 'Camilan', 'is_active' => true]);

        $this->patch(route('owner.menu-categories.toggle-status', $category))
            ->assertRedirect(route('owner.menu-categories.index'));

        $this->assertDatabaseHas('menu_categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }

    public function test_invalid_or_duplicate_category_input_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        MenuCategory::create(['name' => 'Makanan']);

        $this->from(route('owner.menu-categories.index'))
            ->post(route('owner.menu-categories.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->from(route('owner.menu-categories.index'))
            ->post(route('owner.menu-categories.store'), ['name' => 'Makanan'])
            ->assertSessionHasErrors('name');
    }

    public function test_only_owner_can_access_category_endpoints(): void
    {
        $category = MenuCategory::create(['name' => 'Makanan']);
        $kasir = $this->userWithRole('kasir');

        $this->actingAs($kasir)
            ->get(route('owner.menu-categories.index'))
            ->assertForbidden();

        $this->actingAs($kasir)
            ->get(route('owner.menu-categories.edit', $category))
            ->assertForbidden();

        $this->actingAs($kasir)
            ->post(route('owner.menu-categories.store'), ['name' => 'Minuman'])
            ->assertForbidden();

        $this->actingAs($kasir)
            ->patch(route('owner.menu-categories.toggle-status', $category))
            ->assertForbidden();

        $this->actingAs($kasir)
            ->put(route('owner.menu-categories.update', $category), ['name' => 'Nama Baru'])
            ->assertForbidden();

        auth()->logout();
        $this->get(route('owner.menu-categories.index'))->assertRedirect(route('login'));

        $this->assertDatabaseMissing('menu_categories', ['name' => 'Minuman']);
    }

    public function test_missing_category_returns_not_found(): void
    {
        $this->actingAs($this->userWithRole('owner'));

        $this->get(route('owner.menu-categories.edit', 99999))->assertNotFound();
        $this->put(route('owner.menu-categories.update', 99999), ['name' => 'Tidak Ada'])->assertNotFound();
        $this->patch(route('owner.menu-categories.toggle-status', 99999))->assertNotFound();
    }
}
