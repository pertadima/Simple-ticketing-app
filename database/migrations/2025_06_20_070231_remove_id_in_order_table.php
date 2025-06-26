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
        Schema::table('orders', function (Blueprint $table) {
            // 1. Drop the column
            $table->dropColumn('id_card_number');
            $table->dropColumn('id_card_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('id_card_number')->nullable();
            $table->string('id_card_type')->nullable();
        });
    }
};
