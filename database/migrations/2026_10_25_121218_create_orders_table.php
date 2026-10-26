<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// order_items untuk menyimpan detail menu dalam setiap pesanan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('table_id')
                ->nullable()
                ->constrained('tables')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('order_code')->unique();
            $table->string('tracking_token')->unique();

            $table->string('customer_name');
            $table->string('customer_phone')->nullable();

            $table->string('order_channel', 30);
            $table->string('order_type', 30);
            $table->string('order_status', 30);

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};