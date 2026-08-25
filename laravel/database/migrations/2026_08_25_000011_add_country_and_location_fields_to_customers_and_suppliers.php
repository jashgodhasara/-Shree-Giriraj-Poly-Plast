<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'country')) {
                $table->string('country', 100)->default('India')->after('state');
            }
            if (!Schema::hasColumn('customers', 'city')) {
                $table->string('city', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'pincode')) {
                $table->string('pincode', 20)->nullable()->after('state');
            }
            if (!Schema::hasColumn('customers', 'tax_type')) {
                $table->string('tax_type', 50)->default('Regular')->after('gstin');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'country')) {
                $table->string('country', 100)->default('India')->after('state');
            }
            if (!Schema::hasColumn('suppliers', 'city')) {
                $table->string('city', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'pincode')) {
                $table->string('pincode', 20)->nullable()->after('state');
            }
            if (!Schema::hasColumn('suppliers', 'tax_type')) {
                $table->string('tax_type', 50)->default('Regular')->after('gstin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('customers', 'country')) $cols[] = 'country';
            if (Schema::hasColumn('customers', 'city')) $cols[] = 'city';
            if (Schema::hasColumn('customers', 'pincode')) $cols[] = 'pincode';
            if (Schema::hasColumn('customers', 'tax_type')) $cols[] = 'tax_type';
            if (!empty($cols)) $table->dropColumn($cols);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('suppliers', 'country')) $cols[] = 'country';
            if (Schema::hasColumn('suppliers', 'city')) $cols[] = 'city';
            if (Schema::hasColumn('suppliers', 'pincode')) $cols[] = 'pincode';
            if (Schema::hasColumn('suppliers', 'tax_type')) $cols[] = 'tax_type';
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
