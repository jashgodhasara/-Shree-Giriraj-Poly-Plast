<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin if not exists
        User::firstOrCreate(
            ['email' => 'admin@shreegiriraj.com'],
            [
                'name'      => 'Admin',
                'email'     => 'admin@shreegiriraj.com',
                'password'  => Hash::make('Admin@1234'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Default admin user created: admin@shreegiriraj.com / Admin@1234');
    }
}
