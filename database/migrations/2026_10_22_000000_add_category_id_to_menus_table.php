<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('menu_categories')
                ->nullOnDelete();
            $table->string('category', 255)->change();
        });

        $this->backfillCategoryRelations();
    }

    private function backfillCategoryRelations(): void
    {
        // Preserve legacy text while connecting each menu to a category.
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
        Schema::table('menus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        // Keep the widened legacy text column to avoid truncating names
        // that may have been saved after this migration.
    }
};
