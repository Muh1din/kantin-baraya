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
            ['name' => 'cashier', 'email' => 'kasir@kantinbaraya.test', 'role' => 'cashier'],
            ['name' => 'customer', 'email' => 'customer@kantinbaraya.test', 'role' => 'customer'],
            ['name' => 'chef', 'email' => 'koki@kantinbaraya.test', 'role' => 'chef'],
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
