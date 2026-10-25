<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();
            $table->string('name', 150)->unique();
            $table->decimal('current_stock', 18, 3)->unsigned()->default(0);
            $table->decimal('minimum_stock', 18, 3)->unsigned()->default(0);
            $table->decimal('average_cost_price', 18, 2)->unsigned()->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
