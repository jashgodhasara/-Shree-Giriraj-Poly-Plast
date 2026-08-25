<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique(); // e.g. MCH-01, CHILL-01, COMP-01
            $table->string('name'); // e.g. 150 Ton Injection Moulding Machine (Windsor)
            $table->string('category')->default('Moulding Machine'); // Moulding Machine, Auxiliary Equipment, Compressor & Chiller, Electrical & Power, Material Handling, Quality & Lab, Packaging & Tool Room
            $table->string('make_brand')->nullable(); // e.g. Windsor, Ferromatik Milacron, Toshiba, Atlas Copco, Kirloskar
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('tonnage_or_capacity')->nullable(); // e.g. 150 Ton, 250 Ton, 10 HP, 100 CFM, 2 Ton
            $table->decimal('power_rating_kw', 8, 2)->nullable(); // e.g. 22.5 kW, 45 kW
            $table->string('plant_location')->nullable(); // e.g. Bay 1, Line 2, Compressor Room, Tool Room
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 14, 2)->default(0.00);
            $table->date('warranty_expiry')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('status')->default('Operational'); // Operational, Standby, Breakdown, Maintenance / Overhaul, Decommissioned
            $table->string('assigned_operator')->nullable();
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->integer('service_interval_days')->default(90); // default 90 days preventive maintenance cycle
            $table->string('image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('factory_assets')->cascadeOnDelete();
            $table->date('service_date');
            $table->string('service_type'); // Preventive Maintenance (PM), Breakdown Repair, Oil & Filter Replacement, Calibration, Electrical Repair
            $table->decimal('cost', 10, 2)->default(0.00);
            $table->string('technician_name')->nullable();
            $table->string('vendor_name')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->text('problem_reported')->nullable();
            $table->text('action_taken')->nullable();
            $table->string('status_after_service')->default('Operational');
            $table->date('next_service_due')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_logs');
        Schema::dropIfExists('factory_assets');
    }
};
