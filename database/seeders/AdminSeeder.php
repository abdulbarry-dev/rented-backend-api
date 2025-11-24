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
        $this->command->info('Seeding admins...');

        // Check if admins already exist to prevent duplicates
        if (Admin::count() > 0) {
            $this->command->info('Admins already exist. Skipping admin creation.');
            return;
        }

        // Create super admin
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'super',
            'status' => 'active',
            'approved_by' => null,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->command->info('✓ Super admin created: ' . $superAdmin->email . ' (password: password123)');

        // Create additional super admin for redundancy
        $superAdmin2 = Admin::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'super',
            'status' => 'active',
            'approved_by' => null,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->command->info('✓ Additional super admin created: ' . $superAdmin2->email);

        // Create active moderators
        $moderators = [
            ['name' => 'Bob Moderator', 'email' => 'bob@admin.com'],
            ['name' => 'Carol Smith', 'email' => 'carol@admin.com'],
            ['name' => 'David Wilson', 'email' => 'david@admin.com'],
        ];

        foreach ($moderators as $mod) {
            Admin::create([
                'name' => $mod['name'],
                'email' => $mod['email'],
                'password_hash' => Hash::make('password123'),
                'role' => 'moderator',
                'status' => 'active',
                'approved_by' => $superAdmin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            $this->command->info('✓ Moderator created: ' . $mod['email']);
        }

        // Create pending moderators for approval workflow testing
        $pendingModerators = [
            ['name' => 'Emma Pending', 'email' => 'emma@admin.com'],
            ['name' => 'Frank Applicant', 'email' => 'frank@admin.com'],
        ];

        foreach ($pendingModerators as $pending) {
            Admin::create([
                'name' => $pending['name'],
                'email' => $pending['email'],
                'password_hash' => Hash::make('password123'),
                'role' => 'moderator',
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null,
            ]);

            $this->command->info('✓ Pending admin created: ' . $pending['email']);
        }

        // Create banned admin for testing
        Admin::create([
            'name' => 'George Banned',
            'email' => 'george@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'moderator',
            'status' => 'banned',
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'rejection_reason' => 'Violation of platform rules and policies.',
        ]);

        $this->command->info('✓ Banned admin created: george@admin.com');

        $totalAdmins = Admin::count();
        $this->command->info("\nAdmin seeding completed! Total admins: {$totalAdmins}");
        $this->command->info("  - Super admins: " . Admin::where('role', 'super')->count());
        $this->command->info("  - Moderators (active): " . Admin::where('role', 'moderator')->where('status', 'active')->count());
        $this->command->info("  - Pending: " . Admin::where('status', 'pending')->count());
        $this->command->info("  - Banned: " . Admin::where('status', 'banned')->count());
    }
}
