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
        // Rename users table to api_users
        Schema::rename('users', 'api_users');
        
        // Update foreign key references
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('user_id')->on('api_users');
        });
        
        Schema::table('voucher_redemptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('user_id')->on('api_users');
        });
        
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Update polymorphic references if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore foreign key references
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('user_id')->on('users');
        });
        
        Schema::table('voucher_redemptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('user_id')->on('users');
        });
        
        // Rename api_users table back to users
        Schema::rename('api_users', 'users');
    }
};
