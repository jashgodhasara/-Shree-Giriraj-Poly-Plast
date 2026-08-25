<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->date('transaction_date')->index();
            $table->string('transaction_type')->index(); 
            // e.g. Opening Stock, Purchase, Purchase Return, Sales, Sales Return, Stock Adjustment, Stock Transfer In, Stock Transfer Out, Job Work Issue, Job Work Receive, Production, Consumption, Damage, Wastage
            $table->string('reference_module')->nullable()->index(); // Invoices, PurchaseOrders, JobWorkOrders, Production, Adjustments, Transfers
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->string('reference_number')->nullable()->index();
            $table->decimal('quantity_in', 14, 4)->default(0.0000);
            $table->decimal('quantity_out', 14, 4)->default(0.0000);
            $table->string('unit', 50)->default('PCS');
            $table->decimal('converted_quantity', 14, 4)->default(0.0000);
            $table->decimal('rate', 12, 2)->default(0.00);
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->decimal('previous_stock', 14, 4)->default(0.0000);
            $table->decimal('stock_change', 14, 4)->default(0.0000);
            $table->decimal('new_stock', 14, 4)->default(0.0000);
            $table->decimal('average_cost_after', 14, 4)->default(0.0000);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'transaction_date']);
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->date('adjustment_date')->index();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('system_stock', 14, 4);
            $table->decimal('physical_stock', 14, 4);
            $table->decimal('difference_quantity', 14, 4);
            $table->string('adjustment_type', 20); // Increase, Decrease
            $table->string('reason')->default('Physical Count'); // Physical Count, Damage, Expiry, Wastage, Other
            $table->text('remarks')->nullable();
            $table->string('status', 20)->default('Applied'); // Applied, Cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->date('transfer_date')->index();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 14, 4);
            $table->string('unit', 50)->default('PCS');
            $table->decimal('converted_quantity', 14, 4)->default(0.0000);
            $table->string('status', 20)->default('Completed'); // Pending, In Transit, Completed, Cancelled
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('inventory_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // Created, Updated, Deleted, Stock Changed, Adjustment, Transfer
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_audit_logs');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_ledgers');
    }
};
