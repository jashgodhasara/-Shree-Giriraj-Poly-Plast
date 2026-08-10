<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('raw_material_id')->constrained('materials')->onDelete('restrict');
            $table->decimal('raw_material_used_kg', 10, 2);
            $table->foreignId('additive_id')->nullable()->constrained('materials')->onDelete('set null');
            $table->decimal('additive_used_kg', 10, 2)->nullable();
            $table->foreignId('final_product_id')->constrained('materials')->onDelete('restrict');
            $table->integer('final_product_qty_pcs');
            $table->decimal('salvage_qty_kg', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_logs');
    }
};
