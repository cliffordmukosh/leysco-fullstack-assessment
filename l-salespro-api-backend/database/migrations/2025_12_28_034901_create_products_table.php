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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); // Unique product SKU
            $table->string('name'); // Product name
            $table->foreignId('category_id')->constrained()->onDelete('restrict'); // Link to categories
            $table->string('subcategory')->nullable(); // Subcategory e.g., 'Mineral Oils'
            $table->text('description')->nullable(); // Product description
            $table->decimal('price', 12, 2); // Price (KES)
            $table->decimal('tax_rate', 5, 2)->default(16.00); // Tax rate (%)
            $table->string('unit'); // Unit e.g., 'Liter'
            $table->string('packaging'); // Packaging e.g., '5L Container'
            $table->integer('min_order_quantity')->default(1); // Min order qty
            $table->integer('reorder_level')->default(10); // Reorder threshold for alerts
            $table->softDeletes(); // For soft deletes
            $table->timestamps();

            $table->index('sku'); // Quick lookup by SKU
            $table->index('name'); // For sorting/filtering
            $table->fullText(['name', 'description']); // For full-text search
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};