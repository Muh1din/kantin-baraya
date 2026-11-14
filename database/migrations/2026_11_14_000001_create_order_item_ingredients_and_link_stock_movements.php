<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->string('ingredient_name', 150);
            $table->string('unit_abbreviation', 20);
            $table->decimal('quantity_per_item', 18, 3)->unsigned();
            $table->decimal('required_quantity', 18, 3)->unsigned();
            $table->timestamps();

            $table->unique(['order_item_id', 'ingredient_id']);
            $table->index('ingredient_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dateTime('recipe_snapshotted_at')->nullable()->after('notes');
        });

        Schema::table('ingredient_stock_movements', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->after('ingredient_id')
                ->constrained('orders')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('recipe_snapshotted_at');
        });

        Schema::dropIfExists('order_item_ingredients');
    }
};
