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
        Schema::table('seats', function (Blueprint $table) {
            // 1. Drop the column
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->string('category_id')->nullable();
            $table->foreign('category_id')->references('category_id')->on('ticket_categories')->onDelete('cascade');
        });
    }
};
