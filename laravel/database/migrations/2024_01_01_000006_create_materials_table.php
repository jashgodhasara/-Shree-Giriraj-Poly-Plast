<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Raw Material', 'Additive', 'Final Product']);
            $table->string('name');
            $table->string('unit', 10)->nullable();
            $table->string('grade_variation', 100)->nullable();
            $table->string('temp', 50)->nullable();
            $table->string('size', 50)->nullable();
            $table->decimal('stock_quantity', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
