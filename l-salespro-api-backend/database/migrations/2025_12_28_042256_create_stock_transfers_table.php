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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('restrict'); // Source warehouse
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->onDelete('restrict'); // Destination warehouse
            $table->foreignId('product_id')->constrained()->onDelete('restrict'); // Product transferred
            $table->integer('quantity'); // Transferred quantity
            $table->string('status')->default('pending'); // Status: pending, completed, cancelled
            $table->text('notes')->nullable(); // Optional notes
            $table->foreignId('user_id')->constrained()->onDelete('restrict'); // User who initiated
            $table->timestamps();

            $table->index('status'); // For history filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};