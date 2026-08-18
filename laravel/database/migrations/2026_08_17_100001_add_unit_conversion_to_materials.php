<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // Conversion: how many Kg makes 1 Pcs (e.g. 1 bag = 0.5 Kg)
            $table->decimal('kg_per_pcs', 10, 4)->nullable()->after('stock_quantity')
                  ->comment('How many Kg = 1 Pcs. Set if material tracks both units.');

            // Dual-unit stock: track BOTH Kg and Pcs separately
            $table->decimal('stock_kg', 10, 3)->default(0)->after('kg_per_pcs')
                  ->comment('Stock in Kg (for raw material / weight-based)');
            $table->decimal('stock_pcs', 10, 2)->default(0)->after('stock_kg')
                  ->comment('Stock in Pcs (for finished goods / count-based)');

            // Secondary unit label (e.g. "Pcs", "Bags", "Rolls")
            $table->string('secondary_unit', 20)->nullable()->after('unit')
                  ->comment('Second unit label e.g. Pcs when primary is Kg');
        });

        // Add unit tracking to material_transactions
        Schema::table('material_transactions', function (Blueprint $table) {
            // Which unit was this transaction in
            $table->enum('unit_type', ['Kg', 'Pcs'])->default('Kg')->after('quantity')
                  ->comment('Unit of the transaction quantity');

            // If Pcs transaction, also store equivalent Kg
            $table->decimal('quantity_kg', 10, 3)->nullable()->after('unit_type')
                  ->comment('Equivalent Kg (auto-calculated when unit_type=Pcs)');
            $table->decimal('quantity_pcs', 10, 2)->nullable()->after('quantity_kg')
                  ->comment('Equivalent Pcs (auto-calculated when unit_type=Kg)');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['kg_per_pcs', 'stock_kg', 'stock_pcs', 'secondary_unit']);
        });
        Schema::table('material_transactions', function (Blueprint $table) {
            $table->dropColumn(['unit_type', 'quantity_kg', 'quantity_pcs']);
        });
    }
};
