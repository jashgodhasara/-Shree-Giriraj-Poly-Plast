<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('transporter_id')->nullable()->after('customer_id')
                  ->constrained('transporters')->onDelete('set null');
            $table->string('lr_number', 50)->nullable()->after('transporter_id');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('grand_total');
            $table->string('payment_mode', 30)->nullable()->after('paid_amount'); // Cash/Cheque/NEFT/UPI
            $table->text('notes')->nullable()->after('payment_mode');
        });

        // Update status enum to allow more values
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 20)->default('Unpaid')->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['transporter_id']);
            $table->dropColumn(['transporter_id', 'lr_number', 'paid_amount', 'payment_mode', 'notes']);
        });
    }
};
