<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat user dengan role tertentu.
     */
    private function createUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
        ]);

        return User::factory()->create([
            'name' => ucfirst($roleName) . ' Test',
            'email' => $roleName . '@kantinbaraya.test',
            'password' => 'password123',
            'role_id' => $role->id,
        ]);
    }

    /**
     * AUTH-01
     * Halaman login dapat diakses oleh tamu.
     */
    public function test_guest_can_open_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    /**
     * AUTH-02 sampai AUTH-04
     * Login berhasil dan diarahkan ke dashboard sesuai role.
     */
    public function test_users_are_redirected_to_their_role_dashboard(): void
    {
        $scenarios = [
            'owner' => 'owner.dashboard',
            'kasir' => 'kasir.dashboard',
            'pelanggan' => 'pelanggan.dashboard',
        ];

        foreach ($scenarios as $roleName => $dashboardRoute) {
            $user = $this->createUserWithRole($roleName);

            $response = $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password123',
            ]);

            $response->assertRedirect(route($dashboardRoute));

            $this->assertAuthenticatedAs($user);

            Auth::logout();
        }
    }

    /**
     * AUTH-05
     * Login gagal ketika password salah.
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        $user = $this->createUserWithRole('owner');

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * AUTH-06
     * Login gagal ketika email tidak terdaftar.
     */
    public function test_login_fails_with_unregistered_email(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'unknown@kantinbaraya.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * AUTH-07 sampai AUTH-10
     * Validasi input login.
     */
    public function test_login_validates_required_fields_and_email_format(): void
    {
        $scenarios = [
            [
                'data' => [
                    'password' => 'password123',
                ],
                'errors' => ['email'],
            ],
            [
                'data' => [
                    'email' => 'owner@kantinbaraya.test',
                ],
                'errors' => ['password'],
            ],
            [
                'data' => [
                    'email' => 'bukan-email',
                    'password' => 'password123',
                ],
                'errors' => ['email'],
            ],
            [
                'data' => [
                    'email' => '',
                    'password' => '',
                ],
                'errors' => ['email', 'password'],
            ],
        ];

        foreach ($scenarios as $scenario) {
            $response = $this->post(
                route('login.store'),
                $scenario['data']
            );

            $response->assertSessionHasErrors($scenario['errors']);

            $this->assertGuest();
        }
    }

    /**
     * AUTH-11
     * Login dengan remember me.
     */
    public function test_user_can_login_with_remember_me(): void
    {
        $user = $this->createUserWithRole('owner');

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('owner.dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->assertNotNull($user->fresh()->getRememberToken());
    }

    /**
     * AUTH-13
     * Logout mengakhiri autentikasi.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->createUserWithRole('owner');

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /**
     * AUTH-14 dan AUTH-25
     * Tamu tidak dapat membuka dashboard.
     */
    public function test_guest_cannot_access_any_dashboard(): void
    {
        $dashboardRoutes = [
            'owner.dashboard',
            'kasir.dashboard',
            'pelanggan.dashboard',
        ];

        foreach ($dashboardRoutes as $dashboardRoute) {
            $response = $this->get(route($dashboardRoute));

            $response->assertRedirect(route('login'));
        }
    }

    /**
     * AUTH-15 sampai AUTH-23
     * Setiap role hanya boleh membuka dashboard miliknya.
     */
    public function test_users_can_only_access_their_own_dashboard(): void
    {
        $scenarios = [
            'owner' => [
                'owner.dashboard' => true,
                'kasir.dashboard' => false,
                'pelanggan.dashboard' => false,
            ],
            'kasir' => [
                'owner.dashboard' => false,
                'kasir.dashboard' => true,
                'pelanggan.dashboard' => false,
            ],
            'pelanggan' => [
                'owner.dashboard' => false,
                'kasir.dashboard' => false,
                'pelanggan.dashboard' => true,
            ],
        ];

        foreach ($scenarios as $roleName => $dashboardRoutes) {
            $user = $this->createUserWithRole($roleName);

            foreach ($dashboardRoutes as $dashboardRoute => $allowed) {
                $response = $this->actingAs($user)
                    ->get(route($dashboardRoute));

                if ($allowed) {
                    $response->assertOk();
                } else {
                    $response->assertForbidden();
                }
            }

            Auth::logout();
        }
    }

    /**
     * AUTH-24
     * User dengan role yang tidak dikenal tidak boleh login.
     */
    public function test_user_with_unauthorized_role_cannot_login(): void
    {
        $user = $this->createUserWithRole('manager');

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
