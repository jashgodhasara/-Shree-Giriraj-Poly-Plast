<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_works', function (Blueprint $table) {
            $table->id();
            $table->string('party_name');           // who gives the job
            $table->string('phone', 20)->nullable();
            $table->string('work_type')->nullable(); // e.g. Weaving, Printing, Cutting
            $table->decimal('rate', 10, 2)->nullable();
            $table->string('unit', 20)->nullable();  // per kg, per piece
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_works');
    }
};
