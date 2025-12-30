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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Link to product
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade'); // Link to warehouse
            $table->integer('quantity')->default(0); // Current stock quantity
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']); // Unique per product-warehouse
            $table->index(['product_id', 'warehouse_id']); // For quick stock queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};