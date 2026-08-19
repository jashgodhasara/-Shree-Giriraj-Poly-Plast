<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('job_work_number', 50)->unique();
            $table->foreignId('client_id')->constrained('job_work_clients')->cascadeOnDelete();
            $table->date('order_date');
            $table->date('due_date')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->enum('status', [
                'Draft',
                'Material Received',
                'In Production',
                'Partially Completed',
                'Completed',
                'Delivered',
                'Cancelled'
            ])->default('Material Received');
            $table->enum('rounding_method', ['floor', 'round', 'ceil', 'decimal'])->default('floor');

            // Summary Totals
            $table->decimal('total_received_weight_kg', 14, 4)->default(0);
            $table->decimal('total_gross_pieces', 14, 2)->default(0);
            $table->decimal('total_wastage_pieces', 14, 2)->default(0);
            $table->decimal('total_net_pieces', 14, 2)->default(0);
            $table->decimal('total_delivered_pieces', 14, 2)->default(0);
            $table->decimal('total_balance_pieces', 14, 2)->default(0);

            // Financials
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('additional_charges', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('balance_amount', 14, 2)->default(0);

            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['client_id', 'order_date', 'status']);
        });

        Schema::create('job_work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_work_order_id')->constrained('job_work_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Received Material by Weight
            $table->decimal('received_weight', 14, 4);
            $table->string('received_weight_unit', 20)->default('KG'); // KG, Gram, Milligram, Ton
            $table->decimal('received_weight_grams', 16, 4);

            // Product Master Weight Snapshot (Historical Preservation)
            $table->decimal('product_weight', 14, 4);
            $table->string('product_weight_unit', 20)->default('Gram');
            $table->decimal('product_weight_grams', 16, 4);

            // Calculated Quantities
            $table->decimal('gross_quantity', 14, 4)->default(0);
            $table->enum('wastage_type', ['percentage', 'fixed', 'none'])->default('percentage');
            $table->decimal('wastage_percentage', 6, 2)->default(0);
            $table->decimal('wastage_quantity', 14, 4)->default(0);
            $table->decimal('net_quantity', 14, 4)->default(0);
            $table->decimal('delivered_quantity', 14, 4)->default(0);
            $table->decimal('balance_quantity', 14, 4)->default(0);

            // Pricing
            $table->enum('rate_type', ['per_piece', 'per_kg', 'fixed'])->default('per_piece');
            $table->decimal('rate', 12, 4)->default(0);
            $table->decimal('amount', 14, 2)->default(0);

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['job_work_order_id', 'product_id']);
        });

        Schema::create('job_work_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_work_order_id')->constrained('job_work_orders')->cascadeOnDelete();
            $table->string('delivery_number', 50)->unique();
            $table->date('delivery_date');
            $table->string('challan_number', 100)->nullable();
            $table->string('vehicle_number', 50)->nullable();
            $table->foreignId('transporter_id')->nullable()->constrained('transporters')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('job_work_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_work_delivery_id')->constrained('job_work_deliveries')->cascadeOnDelete();
            $table->foreignId('job_work_order_item_id')->constrained('job_work_order_items')->cascadeOnDelete();
            $table->decimal('delivered_quantity', 14, 4);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('job_work_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_work_order_id')->constrained('job_work_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50); // e.g. 'Created', 'Status Changed', 'Delivery Recorded', 'Edited'
            $table->string('field_name', 100)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_work_audit_logs');
        Schema::dropIfExists('job_work_delivery_items');
        Schema::dropIfExists('job_work_deliveries');
        Schema::dropIfExists('job_work_order_items');
        Schema::dropIfExists('job_work_orders');
    }
};
