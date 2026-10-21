<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_redirected_to_dashboard_for_their_role(): void
    {
        $role = Role::create(['name' => 'kasir']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('kasir.dashboard'));

        $this->get(route('kasir.dashboard'))->assertOk()->assertSee('Dashboard Kasir');
    }

    public function test_user_can_log_out_from_dashboard(): void
    {
        $role = Role::create(['name' => 'owner']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_login_rejects_invalid_password(): void
    {
        $role = Role::create(['name' => 'pelanggan']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
