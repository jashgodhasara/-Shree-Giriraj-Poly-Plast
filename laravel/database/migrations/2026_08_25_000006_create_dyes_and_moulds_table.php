<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dyes_and_moulds', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. DYE-001, MLD-CAP-28
            $table->string('name'); // e.g. 500ml HDPE Bottle Mould, 28mm Flip Top Cap Mould
            $table->string('mould_type')->default('Injection Mould'); // Injection Mould, Blow Mould, Extrusion Die, Compression Mould
            $table->integer('cavities')->default(1); // e.g. 1, 2, 4, 8, 16, 24
            $table->string('ownership_type')->default('Company Owned'); // Company Owned, Client Owned
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('compatible_machines')->nullable(); // e.g. 150T Windsor, 200T Ferromatik
            $table->string('rack_location')->nullable(); // e.g. Tool Room Rack A - Shelf 2
            $table->string('status')->default('Ready / In Storage'); // Ready / In Storage, Mounted on Machine, Under Maintenance, Scrapped
            $table->unsignedBigInteger('total_shots_count')->default(0);
            $table->unsignedBigInteger('service_interval_shots')->default(50000);
            $table->date('last_serviced_date')->nullable();
            $table->date('next_service_due_date')->nullable();
            $table->decimal('purchase_cost', 12, 2)->default(0.00);
            $table->date('fabrication_date')->nullable();
            $table->string('image')->nullable();
            $table->json('specifications')->nullable(); // weight_kg, core_cavity_steel, runner_type, cooling_channels
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('dye_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dye_id')->constrained('dyes_and_moulds')->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->string('maintenance_type'); // Preventive Cleaning, Breakdown / Repair, Polishing, Pin Replacement, Overhaul
            $table->unsignedBigInteger('shots_at_service')->default(0);
            $table->decimal('cost', 10, 2)->default(0.00);
            $table->string('performed_by')->nullable(); // In-house Tool Room, Third-party Vendor
            $table->string('vendor_name')->nullable();
            $table->text('work_description')->nullable();
            $table->date('next_due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dye_maintenance_logs');
        Schema::dropIfExists('dyes_and_moulds');
    }
};
