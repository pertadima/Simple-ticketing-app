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
        Schema::table('tickets', function (Blueprint $table) {
            // 1. Drop the column
            $table->dropColumn('seat_number');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->unsignedBigInteger('seat_id')->nullable()->after('ticket_id');
            $table->foreign('seat_id')->references('seat_id')->on('seats')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the column in case of rollback
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('seat_number')->nullable()->after('quota');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign(['seat_id']);
            $table->dropColumn('seat_id');
        });
    }
};
