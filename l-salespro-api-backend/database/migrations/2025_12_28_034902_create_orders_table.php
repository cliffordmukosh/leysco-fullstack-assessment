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
            $table->string('order_number')->unique(); // Unique order number (generated)
            $table->foreignId('customer_id')->constrained()->onDelete('restrict'); // Link to customer
            $table->foreignId('user_id')->constrained()->onDelete('restrict'); // Sales rep user
            $table->string('status')->default('Pending'); // Status: Pending, Confirmed, etc.
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable(); // Order-level discount type
            $table->decimal('discount_value', 12, 2)->default(0.00); // Discount value
            $table->decimal('discount_amount', 15, 2)->default(0.00); // Calculated discount
            $table->decimal('subtotal', 15, 2); // Subtotal before discounts/tax
            $table->decimal('tax_amount', 15, 2); // Total tax
            $table->decimal('total_amount', 15, 2); // Final total
            $table->text('notes')->nullable(); // Optional notes
            $table->timestamps();

            $table->index('order_number'); // Quick lookup
            $table->index('status'); // For filtering by status
            $table->index('customer_id'); // For customer history
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