<?php

namespace Tests\Feature\Owner;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function menuData(MenuCategory $category, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nasi Goreng',
            'description' => 'Nasi goreng spesial',
            'category_id' => $category->id,
            'price' => 15000,
            'stock' => 12,
        ], $overrides);
    }

    private function createMenu(MenuCategory $category, array $overrides = []): Menu
    {
        $data = $this->menuData($category, $overrides);
        $data['category'] = $category->name;

        return Menu::create($data);
    }

    public function test_owner_can_view_menu_list(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $category = MenuCategory::create(['name' => 'Makanan']);
        $this->createMenu($category);

        $this->get(route('owner.menus.index'))
            ->assertOk()
            ->assertSee('Nasi Goreng')
            ->assertSee('Makanan')
            ->assertSee('Tambah menu');
    }

    public function test_owner_can_add_a_menu(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $category = MenuCategory::create(['name' => 'Makanan']);

        $this->post(route('owner.menus.store'), $this->menuData($category))
            ->assertRedirect(route('owner.menus.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('menus', [
            'name' => 'Nasi Goreng',
            'category_id' => $category->id,
            'category' => 'Makanan',
            'price' => 15000,
            'stock' => 12,
            'is_available' => true,
        ]);
    }

    public function test_owner_can_edit_a_menu_and_change_its_category(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $oldCategory = MenuCategory::create(['name' => 'Makanan']);
        $newCategory = MenuCategory::create(['name' => 'Menu Utama']);
        $menu = $this->createMenu($oldCategory);

        $this->put(route('owner.menus.update', $menu), $this->menuData($newCategory, [
            'name' => 'Nasi Goreng Spesial',
            'price' => 18000,
        ]))->assertRedirect(route('owner.menus.index'));

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => 'Nasi Goreng Spesial',
            'category_id' => $newCategory->id,
            'category' => 'Menu Utama',
            'price' => 18000,
        ]);
    }

    public function test_owner_can_toggle_menu_availability_without_deleting_it(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $category = MenuCategory::create(['name' => 'Makanan']);
        $menu = $this->createMenu($category);

        $this->patch(route('owner.menus.toggle-status', $menu))
            ->assertRedirect(route('owner.menus.index'));

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'is_available' => false,
        ]);
    }

    public function test_invalid_menu_fields_are_rejected(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $category = MenuCategory::create(['name' => 'Makanan']);

        $this->from(route('owner.menus.index'))
            ->post(route('owner.menus.store'), $this->menuData($category, [
                'name' => '',
                'price' => -1,
                'stock' => -1,
            ]))
            ->assertSessionHasErrors(['name', 'price', 'stock']);
    }

    public function test_category_must_exist_and_be_active_for_new_menu(): void
    {
        $this->actingAs($this->userWithRole('owner'));
        $inactiveCategory = MenuCategory::create(['name' => 'Lama', 'is_active' => false]);

        $this->from(route('owner.menus.index'))
            ->post(route('owner.menus.store'), [
                'name' => 'Menu Salah',
                'category_id' => 99999,
                'price' => 10000,
                'stock' => 5,
            ])
            ->assertSessionHasErrors('category_id');

        $this->from(route('owner.menus.index'))
            ->post(route('owner.menus.store'), [
                'name' => 'Menu Nonaktif',
                'category_id' => $inactiveCategory->id,
                'price' => 10000,
                'stock' => 5,
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('menus', ['name' => 'Menu Salah']);
        $this->assertDatabaseMissing('menus', ['name' => 'Menu Nonaktif']);
    }

    public function test_category_relation_migration_preserves_legacy_menu_category_text(): void
    {
        $menu = Menu::create([
            'name' => 'Menu Lama',
            'category' => 'makanan',
            'price' => 12000,
            'stock' => 3,
        ]);

        $migration = require database_path('migrations/2026_10_22_000000_add_category_id_to_menus_table.php');
        $migration->down();
        $migration->up();

        $menu->refresh();

        $this->assertSame('makanan', $menu->category);
        $this->assertNotNull($menu->category_id);
        $this->assertSame('makanan', $menu->menuCategory->name);
        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'category' => 'makanan',
            'category_id' => $menu->category_id,
        ]);
    }

    public function test_only_owner_can_access_menu_endpoints(): void
    {
        $menuCategory = MenuCategory::create(['name' => 'Makanan']);
        $menu = $this->createMenu($menuCategory);
        $kasir = $this->userWithRole('kasir');

        $this->actingAs($kasir)->get(route('owner.menus.index'))->assertForbidden();
        $this->actingAs($kasir)->post(route('owner.menus.store'), $this->menuData($menuCategory))->assertForbidden();
        $this->actingAs($kasir)->get(route('owner.menus.edit', $menu))->assertForbidden();
        $this->actingAs($kasir)->put(route('owner.menus.update', $menu), $this->menuData($menuCategory))->assertForbidden();
        $this->actingAs($kasir)->patch(route('owner.menus.toggle-status', $menu))->assertForbidden();

        auth()->logout();
        $this->get(route('owner.menus.index'))->assertRedirect(route('login'));
    }

    public function test_missing_menu_returns_not_found(): void
    {
        $this->actingAs($this->userWithRole('owner'));

        $this->get(route('owner.menus.edit', 99999))->assertNotFound();
        $this->put(route('owner.menus.update', 99999), [])->assertNotFound();
        $this->patch(route('owner.menus.toggle-status', 99999))->assertNotFound();
    }
}
