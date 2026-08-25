<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // e.g. Kilogram, Gram, Ton, Piece
            $table->string('code')->unique();    // e.g. KG, G, TON, PCS, BOX, BAG, MTR, LTR
            $table->string('symbol')->nullable(); // e.g. kg, g, pcs
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('to_unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('conversion_factor', 12, 6); // e.g., 1 KG = 1000 G => factor 1000
            $table->string('operator', 5)->default('*'); // * or /
            $table->timestamps();

            $table->unique(['from_unit_id', 'to_unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('units');
    }
};
