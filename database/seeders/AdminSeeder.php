<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admins already exist to prevent duplicates
        if (Admin::count() > 0) {
            $this->command->info('Admins already exist. Skipping seeding.');
            return;
        }

        $this->command->info('Seeding admins table...');

        // Create super admin directly without factory to ensure it goes to admins table
        $superAdmin = Admin::create([
            'name' => 'John Super',
            'email' => 'super@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'super',
            'status' => 'active',
            'approved_by' => null,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->command->info('Super admin created: ' . $superAdmin->email);

        // Create active moderator directly
        $moderatorAdmin = Admin::create([
            'name' => 'Jane Moderator',
            'email' => 'mod@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'moderator',
            'status' => 'active',
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->command->info('Moderator admin created: ' . $moderatorAdmin->email);

        // Create a pending moderator for testing approval workflow
        $pendingAdmin = Admin::create([
            'name' => 'Pending Admin',
            'email' => 'pending@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'moderator',
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ]);

        $this->command->info('Pending admin created: ' . $pendingAdmin->email);

        // Create a banned admin for testing
        $bannedAdmin = Admin::create([
            'name' => 'Banned Admin',
            'email' => 'banned@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'moderator',
            'status' => 'banned',
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'rejection_reason' => 'Violation of platform rules',
        ]);

        $this->command->info('Banned admin created: ' . $bannedAdmin->email);

        $this->command->info('Admin seeding completed successfully!');
        $this->command->info('Total admins created: ' . Admin::count());
    }
}
