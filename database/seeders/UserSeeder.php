<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'owner', 'email' => 'owner@kantinbaraya.test', 'role' => 'owner'],
            ['name' => 'cashier', 'email' => 'kasir@kantinbaraya.test', 'role' => 'kasir'],
            ['name' => 'customer', 'email' => 'pelanggan@kantinbaraya.test', 'role' => 'pelanggan'],
            ['name' => 'chef', 'email' => 'koki@kantinbaraya.test', 'role' => 'koki'],
        ];

        foreach ($accounts as $account) {
            $role = Role::where('name', $account['role'])->firstOrFail();

            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role_id' => $role->id,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
