<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('cash_received')->nullable()->after('payment_method');
            $table->unsignedBigInteger('change_due')->nullable()->after('cash_received');
            $table->string('idempotency_key', 100)->nullable()->unique()->after('change_due');
            $table->char('idempotency_hash', 64)->nullable()->after('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn(['cash_received', 'change_due', 'idempotency_key', 'idempotency_hash']);
        });
    }
};
