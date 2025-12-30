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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique(); // Unique user identifier
            $table->string('first_name'); // User's first name
            $table->string('last_name'); // User's last name
            $table->string('role')->nullable(); // Role like 'Sales Manager'
            $table->json('permissions')->nullable(); // Array of permissions
            $table->string('status')->default('active'); // Status: active/inactive
            $table->json('notification_preferences')->nullable(); // User notification prefs (JSON)

            $table->index('role'); // For role-based queries
            $table->index('status'); // For status filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'first_name', 'last_name', 'role', 'permissions', 'status', 'notification_preferences']);
        });
    }
};