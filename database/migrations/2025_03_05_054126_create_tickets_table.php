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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->foreignId('event_id')->constrained('events', 'event_id');
            $table->foreignId('category_id')->constrained('ticket_categories', 'category_id');
            $table->foreignId('type_id')->constrained('ticket_types', 'type_id');
            $table->decimal('price', 10, 2);
            $table->integer('quota');
            $table->integer('sold_count')->default(0);
            $table->integer('min_age')->nullable();
            $table->boolean('requires_id_verification')->default(false);
            $table->unique(['event_id', 'category_id', 'type_id']);
            $table->timestamps();
        });

         // For databases that support check constraints
         if (DB::getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION) >= '8.0.16') {
            DB::statement('
                ALTER TABLE tickets 
                ADD CONSTRAINT chk_age_verification 
                CHECK (
                    (requires_id_verification = TRUE AND min_age IS NOT NULL) 
                    OR 
                    (requires_id_verification = FALSE)
                )
            ');
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
