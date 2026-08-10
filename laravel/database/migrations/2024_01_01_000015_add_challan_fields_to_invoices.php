<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_terms', 100)->nullable()->after('status');  // ADVANCE / 30 DAYS etc.
            $table->string('po_number', 100)->nullable()->after('payment_terms'); // buyer PO number
            $table->string('po_date', 30)->nullable()->after('po_number');
            $table->string('delivery_at', 255)->nullable()->after('po_date');   // delivery address
            $table->string('eway_bill_no', 50)->nullable()->after('delivery_at');
            $table->string('challan_number', 50)->nullable()->after('eway_bill_no');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_terms','po_number','po_date','delivery_at','eway_bill_no','challan_number']);
        });
    }
};
