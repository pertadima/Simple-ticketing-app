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
        if (DB::getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION) >= '8.0.16') {
            DB::statement('ALTER TABLE tickets DROP CONSTRAINT chk_age_verification');
        }
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('min_age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->integer('min_age')->nullable();
        });
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
};
