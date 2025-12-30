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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Unique code e.g., 'NCW'
            $table->string('name'); // Warehouse name
            $table->string('type'); // Type e.g., 'Main'
            $table->text('address'); // Full address
            $table->string('manager_email')->nullable(); // Manager email
            $table->string('phone')->nullable(); // Phone number
            $table->integer('capacity')->nullable(); // Capacity (units)
            $table->decimal('latitude', 10, 8)->nullable(); // Latitude
            $table->decimal('longitude', 11, 8)->nullable(); // Longitude
            $table->timestamps();

            $table->index('code'); // Quick lookup by code
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};