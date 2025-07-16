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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('table_number'); 
            $table->integer('queue_number'); 
            $table->enum('status', ['Menunggu', 'Selesai'])->default('Menunggu');
            $table->boolean('is_paid')->default(false);
            $table->unsignedBigInteger('receipt_id')->nullable();
            $table->foreign('receipt_id')->references('id')->on('receipts')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
