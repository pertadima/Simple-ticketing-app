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
        Schema::create('event_ticket_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('type_id');
            $table->boolean('has_seat_number')->default(false);
            $table->timestamps();

            $table->unique(['event_id', 'type_id']);
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->foreign('type_id')->references('type_id')->on('ticket_types')->onDelete('cascade');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('has_seat_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_ticket_types');
        
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('has_seat_number')->default(false)->after('requires_id_verification');
        });
    }
};
