<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['Customer', 'Supplier', 'Investor', 'Job Work']);
            $table->unsignedBigInteger('entity_id');
            $table->date('transaction_date');
            $table->enum('type', ['Debit', 'Credit']);
            $table->decimal('amount', 15, 2);
            $table->string('hsn_code', 50)->nullable();
            $table->string('csm_code', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
