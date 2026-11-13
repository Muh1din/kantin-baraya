<?php

namespace Tests\Feature\Owner;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName, string $email): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create([
            'email' => $email,
            'role_id' => $role->id,
        ]);
    }

    public function test_owner_can_view_only_cashier_and_chef_accounts(): void
    {
        $this->actingAs($this->userWithRole('owner', 'owner@kantin.test'));
        $cashier = $this->userWithRole('cashier', 'cashier@kantin.test');
        $chef = $this->userWithRole('chef', 'chef@kantin.test');
        $legacyCashier = $this->userWithRole('kasir', 'legacy-cashier@kantin.test');
        $legacyChef = $this->userWithRole('koki', 'legacy-chef@kantin.test');
        $this->userWithRole('customer', 'customer@kantin.test');

        $this->get(route('owner.users.index'))
            ->assertOk()
            ->assertViewHas('users', function ($users) use ($cashier, $chef, $legacyCashier, $legacyChef) {
                return $users->pluck('id')->sort()->values()->all() === collect([
                    $cashier->id,
                    $chef->id,
                    $legacyCashier->id,
                    $legacyChef->id,
                ])->sort()->values()->all();
            })
            ->assertSee($cashier->email)
            ->assertSee($chef->email)
            ->assertSee($legacyCashier->email)
            ->assertSee($legacyChef->email)
            ->assertSee('Kasir')
            ->assertSee('Koki');
    }

    public function test_owner_can_create_cashier_and_chef_accounts(): void
    {
        $this->actingAs($this->userWithRole('owner', 'owner@kantin.test'));

        foreach ([
            ['role' => 'cashier', 'email' => 'new-cashier@kantin.test'],
            ['role' => 'chef', 'email' => 'new-chef@kantin.test'],
        ] as $account) {
            $this->post(route('owner.users.store'), [
                'name' => 'Karyawan Baru',
                'email' => $account['email'],
                'role' => $account['role'],
                'password' => 'secure-pass-123',
                'password_confirmation' => 'secure-pass-123',
            ])->assertRedirect(route('owner.users.index'))
                ->assertSessionHas('status');

            $createdUser = User::query()->where('email', $account['email'])->firstOrFail();
            $this->assertSame($account['role'], $createdUser->role->name);
            $this->assertTrue(Hash::check('secure-pass-123', $createdUser->password));
        }
    }

    public function test_owner_cannot_create_non_employee_account_or_duplicate_email(): void
    {
        $this->actingAs($this->userWithRole('owner', 'owner@kantin.test'));
        $this->userWithRole('customer', 'existing@kantin.test');

        $this->from(route('owner.users.index'))
            ->post(route('owner.users.store'), [
                'name' => 'Not an employee',
                'email' => 'new-customer@kantin.test',
                'role' => 'customer',
                'password' => 'secure-pass-123',
                'password_confirmation' => 'secure-pass-123',
            ])
            ->assertSessionHasErrors('role');

        $this->from(route('owner.users.index'))
            ->post(route('owner.users.store'), [
                'name' => 'Duplicate email',
                'email' => 'existing@kantin.test',
                'role' => 'cashier',
                'password' => 'secure-pass-123',
                'password_confirmation' => 'secure-pass-123',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 2);
    }

    public function test_owner_can_delete_employee_accounts_but_cannot_delete_owner_or_customer_accounts(): void
    {
        $this->actingAs($this->userWithRole('owner', 'owner@kantin.test'));
        $cashier = $this->userWithRole('cashier', 'cashier@kantin.test');
        $chef = $this->userWithRole('chef', 'chef@kantin.test');
        $customer = $this->userWithRole('customer', 'customer@kantin.test');
        $owner = User::query()->where('email', 'owner@kantin.test')->firstOrFail();

        $this->delete(route('owner.users.destroy', $cashier))
            ->assertRedirect(route('owner.users.index'))
            ->assertSessionHas('status');
        $this->assertDatabaseMissing('users', ['id' => $cashier->id]);

        $this->delete(route('owner.users.destroy', $chef))
            ->assertRedirect(route('owner.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $chef->id]);

        $this->delete(route('owner.users.destroy', $owner))->assertNotFound();
        $this->delete(route('owner.users.destroy', $customer))->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }

    public function test_only_owner_can_access_account_management_routes(): void
    {
        $this->userWithRole('owner', 'owner@kantin.test');
        $employee = $this->userWithRole('cashier', 'cashier@kantin.test');

        $this->actingAs($employee)
            ->get(route('owner.users.index'))
            ->assertForbidden();
        $this->post(route('owner.users.store'), [])->assertForbidden();
        $this->delete(route('owner.users.destroy', $employee))->assertForbidden();

        Auth::logout();
        $this->get(route('owner.users.index'))->assertRedirect(route('login'));
    }
}
