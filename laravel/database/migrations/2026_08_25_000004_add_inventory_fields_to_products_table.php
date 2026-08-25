<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('name')->constrained('product_categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('products', 'subcategory')) {
                $table->string('subcategory')->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('products', 'product_code')) {
                $table->string('product_code')->nullable()->index()->after('sku');
            }
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->string('product_type')->default('Finished Goods')->after('product_code'); // Finished Goods, Raw Material, Semi-Finished, Trading
            }
            if (!Schema::hasColumn('products', 'material_id')) {
                $table->foreignId('material_id')->nullable()->after('product_type')->constrained('materials')->nullOnDelete();
            }
            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('material_id');
            }
            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->default('PCS')->after('unit_type');
            }
            if (!Schema::hasColumn('products', 'purchase_unit')) {
                $table->string('purchase_unit')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('products', 'sales_unit')) {
                $table->string('sales_unit')->nullable()->after('purchase_unit');
            }
            if (!Schema::hasColumn('products', 'conversion_factor')) {
                $table->decimal('conversion_factor', 10, 4)->default(1.0000)->after('sales_unit');
            }
            if (!Schema::hasColumn('products', 'opening_stock')) {
                $table->decimal('opening_stock', 12, 4)->default(0.0000)->after('stock_quantity');
            }
            if (!Schema::hasColumn('products', 'minimum_stock')) {
                $table->decimal('minimum_stock', 12, 4)->default(0.0000)->after('opening_stock');
            }
            if (!Schema::hasColumn('products', 'maximum_stock')) {
                $table->decimal('maximum_stock', 12, 4)->default(0.0000)->after('minimum_stock');
            }
            if (!Schema::hasColumn('products', 'reorder_level')) {
                $table->decimal('reorder_level', 12, 4)->default(0.0000)->after('maximum_stock');
            }
            if (!Schema::hasColumn('products', 'purchase_rate')) {
                $table->decimal('purchase_rate', 12, 2)->default(0.00)->after('price');
            }
            if (!Schema::hasColumn('products', 'average_cost')) {
                $table->decimal('average_cost', 12, 4)->default(0.0000)->after('purchase_rate');
            }
            if (!Schema::hasColumn('products', 'wholesale_rate')) {
                $table->decimal('wholesale_rate', 12, 2)->default(0.00)->after('price');
            }
            if (!Schema::hasColumn('products', 'mrp')) {
                $table->decimal('mrp', 12, 2)->default(0.00)->after('wholesale_rate');
            }
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->index()->after('hsn_code');
            }
            if (!Schema::hasColumn('products', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('reorder_level')->constrained('warehouses')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = [
                'category_id', 'subcategory', 'product_code', 'product_type', 'material_id',
                'brand', 'unit', 'purchase_unit', 'sales_unit', 'conversion_factor',
                'opening_stock', 'minimum_stock', 'maximum_stock', 'reorder_level',
                'purchase_rate', 'average_cost', 'wholesale_rate', 'mrp', 'barcode', 'warehouse_id'
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('products', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
