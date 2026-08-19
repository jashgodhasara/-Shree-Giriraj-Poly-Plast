<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_work_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('gstin', 20)->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Migrate any existing parties from job_works table if present
        if (Schema::hasTable('job_works')) {
            $existingParties = DB::table('job_works')->get();
            foreach ($existingParties as $p) {
                DB::table('job_work_clients')->insert([
                    'name'         => $p->party_name ?? 'Client #' . $p->id,
                    'company_name' => $p->party_name ?? null,
                    'phone'        => $p->phone ?? null,
                    'address'      => $p->address ?? null,
                    'notes'        => $p->notes ?? ($p->work_type ? 'Work Type: ' . $p->work_type : null),
                    'is_active'    => true,
                    'created_at'   => $p->created_at ?? now(),
                    'updated_at'   => $p->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_work_clients');
    }
};
