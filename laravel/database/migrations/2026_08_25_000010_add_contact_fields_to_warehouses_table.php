<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'contact_number')) {
                $table->string('contact_number', 50)->nullable()->after('location');
            }
            if (!Schema::hasColumn('warehouses', 'contact_person')) {
                $table->string('contact_person', 150)->nullable()->after('location');
            }
            if (!Schema::hasColumn('warehouses', 'email')) {
                $table->string('email', 150)->nullable()->after('contact_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('warehouses', 'contact_number')) $columns[] = 'contact_number';
            if (Schema::hasColumn('warehouses', 'contact_person')) $columns[] = 'contact_person';
            if (Schema::hasColumn('warehouses', 'email')) $columns[] = 'email';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
