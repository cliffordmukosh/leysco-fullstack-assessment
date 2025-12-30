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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID for ID
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Link to user
            $table->string('type'); // Type e.g., 'order_confirmation'
            $table->string('title'); // Notification title
            $table->text('message'); // Message body
            $table->boolean('is_read')->default(false); // Read status
            $table->timestamp('read_at')->nullable(); // When read
            $table->timestamps();

            $table->index('user_id'); // For user-specific queries
            $table->index('is_read'); // For unread count
            $table->index('type'); // For filtering types
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};