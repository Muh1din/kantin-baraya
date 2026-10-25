<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')
                ->constrained('ingredients')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('movement_type', 30);
            $table->decimal('quantity_change', 18, 3);
            $table->decimal('stock_after', 18, 3)->unsigned();
            $table->string('transaction_key', 120)->unique();
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['ingredient_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_stock_movements');
    }
};
