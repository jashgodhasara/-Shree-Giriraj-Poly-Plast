<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 100)->nullable()->after('name');
            $table->string('unit_type', 50)->default('PCS')->after('sku');
            $table->decimal('weight_per_piece', 12, 4)->nullable()->default(0)->after('unit_type');
            $table->string('weight_unit', 20)->default('Gram')->after('weight_per_piece'); // Gram, KG, Milligram, Ton
            $table->decimal('weight_in_grams', 14, 4)->nullable()->default(0)->after('weight_unit');
            $table->boolean('job_work_applicable')->default(true)->after('weight_in_grams');
            $table->decimal('wastage_percentage', 6, 2)->default(0)->after('job_work_applicable');
            $table->decimal('fixed_wastage', 12, 4)->default(0)->after('wastage_percentage');
            $table->boolean('is_active')->default(true)->after('fixed_wastage');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'sku',
                'unit_type',
                'weight_per_piece',
                'weight_unit',
                'weight_in_grams',
                'job_work_applicable',
                'wastage_percentage',
                'fixed_wastage',
                'is_active',
            ]);
        });
    }
};
