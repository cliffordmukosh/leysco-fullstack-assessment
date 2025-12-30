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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Customer name
            $table->string('type')->nullable(); // Type e.g., 'Garage'
            $table->string('category')->nullable(); // Category e.g., 'A'
            $table->string('contact_person')->nullable(); // Contact person
            $table->string('phone')->nullable(); // Phone number
            $table->string('email')->nullable()->unique(); // Email (unique)
            $table->string('tax_id')->nullable()->unique(); // Tax ID (unique)
            $table->integer('payment_terms')->default(30); // Payment terms (days)
            $table->decimal('credit_limit', 15, 2)->default(0.00); // Credit limit (KES)
            $table->decimal('current_balance', 15, 2)->default(0.00); // Current balance (KES)
            $table->decimal('latitude', 10, 8)->nullable(); // Latitude for mapping
            $table->decimal('longitude', 11, 8)->nullable(); // Longitude for mapping
            $table->text('address')->nullable(); // Full address
            $table->string('territory')->nullable(); // Territory assignment
            $table->softDeletes(); // For soft deletes
            $table->timestamps();

            $table->index('category'); // For categorization filtering
            $table->index('type'); // For type filtering
            $table->index('email'); // Quick lookup by email
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};