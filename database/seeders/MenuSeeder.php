<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            [
                'name' => 'Nasi Goreng',
                'description' => 'Nasi goreng gurih dan lezat.',
                'category' => 'makanan',
                'price' => 15000,
                'image' => null,
                'stock' => 20,
                'is_available' => true,
            ],
            [
                'name' => 'Mie Goreng',
                'description' => 'Mie goreng dengan bumbu pilihan.',
                'category' => 'makanan',
                'price' => 12000,
                'image' => null,
                'stock' => 20,
                'is_available' => true,
            ],
            [
                'name' => 'Ayam Geprek',
                'description' => 'Ayam crispy dengan sambal pedas.',
                'category' => 'makanan',
                'price' => 18000,
                'image' => null,
                'stock' => 20,
                'is_available' => true,
            ],
            [
                'name' => 'Es Teh Manis',
                'description' => 'Teh manis segar dengan es.',
                'category' => 'minuman',
                'price' => 5000,
                'image' => null,
                'stock' => 30,
                'is_available' => true,
            ],
            [
                'name' => 'Es Jeruk',
                'description' => 'Minuman jeruk yang menyegarkan.',
                'category' => 'minuman',
                'price' => 7000,
                'image' => null,
                'stock' => 30,
                'is_available' => true,
            ],
            [
                'name' => 'Kentang Goreng',
                'description' => 'Kentang goreng renyah.',
                'category' => 'snack',
                'price' => 10000,
                'image' => null,
                'stock' => 15,
                'is_available' => true,
            ],
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->updateOrInsert(
                ['name' => $menu['name']],
                array_merge($menu, [
                    'updated_at' => now(),
                ])
            );
        }
    }
}
