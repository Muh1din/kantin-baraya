<?php

namespace Tests\Feature\Inventory;

use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UnitSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_expected_standard_units(): void
    {
        $this->seed(UnitSeeder::class);

        $this->assertDatabaseHas('units', ['name' => 'Gram', 'abbreviation' => 'g']);
        $this->assertDatabaseHas('units', ['name' => 'Kilogram', 'abbreviation' => 'kg']);
        $this->assertDatabaseHas('units', ['name' => 'Mililiter', 'abbreviation' => 'ml']);
        $this->assertDatabaseHas('units', ['name' => 'Liter', 'abbreviation' => 'l']);
        $this->assertDatabaseHas('units', ['name' => 'Buah', 'abbreviation' => 'pcs']);
        $this->assertDatabaseCount('units', 5);
    }

    public function test_running_it_twice_does_not_duplicate_units(): void
    {
        $this->seed(UnitSeeder::class);
        $this->seed(UnitSeeder::class);

        $this->assertDatabaseCount('units', 5);
    }

    public function test_existing_units_are_preserved_without_being_overwritten_or_deleted(): void
    {
        $existingId = DB::table('units')->insertGetId([
            'name' => 'Gram',
            'abbreviation' => 'gram-lama',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seed(UnitSeeder::class);

        $this->assertDatabaseHas('units', [
            'id' => $existingId,
            'name' => 'Gram',
            'abbreviation' => 'gram-lama',
        ]);
        $this->assertDatabaseHas('units', ['name' => 'Kilogram', 'abbreviation' => 'kg']);
        $this->assertDatabaseCount('units', 5);
    }
}
