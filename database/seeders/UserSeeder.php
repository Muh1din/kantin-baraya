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
        $ownerRoleId = Role::where('name', 'owner')->value('id');
        $kasirRoleId = Role::where('name', 'kasir')->value('id');
        $pelangganRoleId = Role::where('name', 'pelanggan')->value('id');

        if (! $ownerRoleId || ! $kasirRoleId || ! $pelangganRoleId) {
            throw new \RuntimeException(
                'Role owner, kasir, dan pelanggan harus tersedia.'
            );
        }

        User::updateOrCreate(
            ['email' => 'owner@kantinbaraya.test'],
            [
                'name' => 'Owner Kantin',
                'password' => Hash::make('rahasia'),
                'role_id' => $ownerRoleId,
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@kantinbaraya.test'],
            [
                'name' => 'Kasir Kantin',
                'password' => Hash::make('rahasia'),
                'role_id' => $kasirRoleId,
            ]
        );

        User::updateOrCreate(
            ['email' => 'pelanggan@kantinbaraya.test'],
            [
                'name' => 'Pelanggan Kantin',
                'password' => Hash::make('rahasia'),
                'role_id' => $pelangganRoleId,
            ]
        );
    }
}
