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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['general', 'specific']);
            $table->decimal('discount', 8, 2);
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->foreignId('event_id')->nullable()->constrained(
                table: 'events', // Table name
                column: 'event_id' // Custom primary key
            );
            $table->dateTime('valid_until');
            $table->unsignedInteger('usage_limit')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
