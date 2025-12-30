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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // User who performed action
            $table->string('action'); // Action e.g., 'created_order'
            $table->string('subject_type')->nullable(); // Polymorphic type e.g., 'Order'
            $table->unsignedBigInteger('subject_id')->nullable(); // Polymorphic ID
            $table->json('properties')->nullable(); // Details (JSON)
            $table->string('ip_address')->nullable(); // IP of request
            $table->text('user_agent')->nullable(); // User agent
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']); // For subject queries
            $table->index('user_id'); // For user activity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};