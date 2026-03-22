<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@acadclear.com'],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@acadclear.com',
                'password' => Hash::make('SuperAdmin@2024'),
                'email_verified_at' => now(),
                'role' => 'super_admin',
            ]
        );

        $this->command->info('Super Admin created successfully!');
        $this->command->warn('Email: superadmin@acadclear.com');
        $this->command->warn('Password: SuperAdmin@2024');
    }
}