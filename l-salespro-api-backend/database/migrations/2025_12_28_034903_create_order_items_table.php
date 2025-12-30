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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Link to order
            $table->foreignId('product_id')->constrained()->onDelete('restrict'); // Link to product
            $table->integer('quantity'); // Ordered quantity
            $table->decimal('unit_price', 12, 2); // Price per unit
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable(); // Line discount type
            $table->decimal('discount_value', 12, 2)->default(0.00); // Discount value
            $table->decimal('discount_amount', 12, 2)->default(0.00); // Calculated discount
            $table->decimal('tax_amount', 12, 2); // Tax for item
            $table->decimal('subtotal', 12, 2); // Item subtotal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};