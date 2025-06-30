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
        Schema::create('seats', function (Blueprint $table) {
            $table->id('seat_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('type_id');
            $table->string('seat_number');
            $table->boolean('is_booked')->default(false);
            $table->timestamps();

            $table->unique(['event_id', 'category_id', 'type_id', 'seat_number']);
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('ticket_categories')->onDelete('cascade');
            $table->foreign('type_id')->references('type_id')->on('ticket_types')->onDelete('cascade');
        });

        // Add seat_number to tickets (nullable)
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('seat_number')->nullable()->after('quota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('seat_number');
        });
    }
};
