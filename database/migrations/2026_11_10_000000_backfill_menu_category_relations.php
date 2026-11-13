<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyCategories = DB::table('menus')
            ->whereNotNull('category')
            ->whereNull('category_id')
            ->distinct()
            ->pluck('category');

        foreach ($legacyCategories as $categoryName) {
            $categoryId = DB::table('menu_categories')
                ->where('name', $categoryName)
                ->value('id');

            if ($categoryId === null) {
                $categoryId = DB::table('menu_categories')->insertGetId([
                    'name' => $categoryName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('menus')
                ->where('category', $categoryName)
                ->whereNull('category_id')
                ->update(['category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        // This is a data backfill; reversing it could detach valid menu data.
    }
};
