<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_includes_example_menus(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(6, Menu::count());
        $this->assertDatabaseHas('menus', ['name' => 'Nasi Goreng']);
    }

    public function test_user_seeder_does_not_create_demo_accounts_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        app(UserSeeder::class)->run();

        $this->assertSame(0, User::count());
    }
}
