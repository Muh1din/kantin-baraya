<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $units = [
            ['name' => 'Gram', 'abbreviation' => 'g'],
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Mililiter', 'abbreviation' => 'ml'],
            ['name' => 'Liter', 'abbreviation' => 'l'],
            ['name' => 'Buah', 'abbreviation' => 'pcs'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->insertOrIgnore([
                ...$unit,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
