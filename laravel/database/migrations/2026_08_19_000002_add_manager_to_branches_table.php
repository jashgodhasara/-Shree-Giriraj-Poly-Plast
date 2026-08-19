<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('manager_name')->nullable()->after('type');
            $table->string('manager_phone', 30)->nullable()->after('manager_name');
            $table->string('manager_email')->nullable()->after('manager_phone');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['manager_name', 'manager_phone', 'manager_email']);
        });
    }
};
