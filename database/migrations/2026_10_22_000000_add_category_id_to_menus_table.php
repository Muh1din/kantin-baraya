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

        // Convert each existing text category into a category record if needed,
        // then link its menus while leaving the original text untouched.
        DB::table('menus')
            ->select(['id', 'category'])
            ->chunkById(500, function ($menus): void {
                foreach ($menus as $menu) {
                    $name = trim((string) $menu->category);

                    if ($name === '') {
                        continue;
                    }

                    $category = DB::table('menu_categories')->where('name', $name)->first();

                    if (! $category) {
                        $categoryId = DB::table('menu_categories')->insertGetId([
                            'name' => $name,
                            'description' => null,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $categoryId = $category->id;
                    }

                    DB::table('menus')->where('id', $menu->id)->update([
                        'category_id' => $categoryId,
                    ]);
                }
            });
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
