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
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade'); // Link to order (nullable if timed out)
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Link to product
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade'); // Link to warehouse
            $table->integer('quantity'); // Reserved quantity
            $table->timestamp('expires_at'); // Expiration for auto-release (30 min)
            $table->string('status')->default('active'); // Status: active, released
            $table->timestamps();

            $table->index('expires_at'); // For timeout processing
            $table->index('status'); // For filtering active
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};