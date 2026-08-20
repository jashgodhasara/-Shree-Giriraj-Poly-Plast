<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('production_logs', function (Blueprint $table) {
            // Salvage percentage (2.00 – 5.00 %)
            $table->decimal('salvage_pct', 5, 2)->default(2.00)
                  ->after('salvage_qty_kg')
                  ->comment('Salvage/scrap as % of raw material used (typically 2–5%)');

            // Effective yield = raw_used - salvage_kg (in Kg)
            $table->decimal('effective_yield_kg', 10, 3)->nullable()
                  ->after('salvage_pct')
                  ->comment('Usable output in Kg after deducting salvage');
        });
    }

    public function down(): void
    {
        Schema::table('production_logs', function (Blueprint $table) {
            $table->dropColumn(['salvage_pct', 'effective_yield_kg']);
        });
    }
};
