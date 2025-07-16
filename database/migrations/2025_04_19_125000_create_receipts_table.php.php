<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->integer('table_number');
            $table->integer('total_price');
            $table->integer('tax_amount');
            $table->integer('service_charge');
            $table->integer('grand_total');
            $table->string('cashier_name')->default('Levi');
            $table->timestamp('paid_at')->useCurrent();
            $table->string('payment_type');
            $table->integer('cash_amount')->nullable();
            $table->integer('change')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
