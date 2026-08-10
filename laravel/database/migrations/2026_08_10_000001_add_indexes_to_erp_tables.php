<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->index(['entity_type', 'entity_id'], 'idx_ledger_entity');
            $table->index('transaction_date', 'idx_ledger_date');
            $table->index('type', 'idx_ledger_type');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('customer_id', 'idx_inv_customer');
            $table->index('status', 'idx_inv_status');
            $table->index('invoice_date', 'idx_inv_date');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index('supplier_id', 'idx_po_supplier');
            $table->index('status', 'idx_po_status');
            $table->index('po_date', 'idx_po_date');
        });

        Schema::table('material_transactions', function (Blueprint $table) {
            $table->index('material_id', 'idx_mt_material');
            $table->index('supplier_id', 'idx_mt_supplier');
            $table->index('transaction_date', 'idx_mt_date');
        });

        Schema::table('production_logs', function (Blueprint $table) {
            $table->index('raw_material_id', 'idx_pl_raw_mat');
            $table->index('final_product_id', 'idx_pl_final_prod');
            $table->index('date', 'idx_pl_date');
        });
    }

    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->dropIndex('idx_ledger_entity');
            $table->dropIndex('idx_ledger_date');
            $table->dropIndex('idx_ledger_type');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_inv_customer');
            $table->dropIndex('idx_inv_status');
            $table->dropIndex('idx_inv_date');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('idx_po_supplier');
            $table->dropIndex('idx_po_status');
            $table->dropIndex('idx_po_date');
        });

        Schema::table('material_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_mt_material');
            $table->dropIndex('idx_mt_supplier');
            $table->dropIndex('idx_mt_date');
        });

        Schema::table('production_logs', function (Blueprint $table) {
            $table->dropIndex('idx_pl_raw_mat');
            $table->dropIndex('idx_pl_final_prod');
            $table->dropIndex('idx_pl_date');
        });
    }
};
