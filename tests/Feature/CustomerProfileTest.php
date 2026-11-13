<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName, array $attributes = []): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::factory()->create(array_merge(['role_id' => $role->id], $attributes));
    }

    public function test_customer_can_view_profile_and_update_name_and_email_without_changing_role(): void
    {
        $customer = $this->userWithRole('customer', ['name' => 'Nama Lama', 'email' => 'lama@example.com']);
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);

        $this->actingAs($customer)
            ->get(route('customer.profile.edit'))
            ->assertOk()
            ->assertSee('Nama Lama')
            ->assertSee('lama@example.com')
            ->assertSee('Ubah kata sandi');

        $this->put(route('customer.profile.update'), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'role_id' => $ownerRole->id,
            'is_admin' => true,
        ])->assertRedirect(route('customer.profile.edit'))->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'role_id' => $customer->role_id,
        ]);
    }

    public function test_customer_email_must_be_unique(): void
    {
        $customer = $this->userWithRole('customer');
        $otherCustomer = $this->userWithRole('customer', ['email' => 'taken@example.com']);

        $this->actingAs($customer)
            ->from(route('customer.profile.edit'))
            ->put(route('customer.profile.update'), [
                'name' => $customer->name,
                'email' => $otherCustomer->email,
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame($customer->email, $customer->fresh()->email);
    }

    public function test_password_change_requires_valid_current_password_and_confirmation(): void
    {
        $customer = $this->userWithRole('customer', ['password' => 'old-password-123']);
        $base = ['name' => $customer->name, 'email' => $customer->email, 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'];

        $this->actingAs($customer)
            ->from(route('customer.profile.edit'))
            ->put(route('customer.profile.update'), $base)
            ->assertSessionHasErrors('current_password');

        $this->from(route('customer.profile.edit'))
            ->put(route('customer.profile.update'), array_merge($base, ['current_password' => 'wrong-password']))
            ->assertSessionHasErrors('current_password');

        $this->put(route('customer.profile.update'), array_merge($base, ['current_password' => 'old-password-123']))
            ->assertRedirect(route('customer.profile.edit'));

        $this->assertTrue(Hash::check('new-password-123', $customer->fresh()->password));
    }

    public function test_profile_routes_are_only_available_to_customers(): void
    {
        $owner = $this->userWithRole('owner');

        $this->actingAs($owner)->get(route('customer.profile.edit'))->assertForbidden();
        $this->put(route('customer.profile.update'), [])->assertForbidden();
    }
}
