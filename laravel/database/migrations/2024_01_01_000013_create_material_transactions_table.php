<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Track raw material in (purchase from supplier) and out (transfers / adjustments)
        Schema::create('material_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->onDelete('restrict');
            $table->enum('type', ['IN', 'OUT']);         // Purchase = IN, Usage/Transfer = OUT
            $table->decimal('quantity', 10, 2);
            $table->decimal('rate', 10, 2)->nullable();  // price per unit
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->date('transaction_date');
            $table->string('reference_no', 100)->nullable();  // challan / bill number
            $table->string('vehicle_no', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_transactions');
    }
};
