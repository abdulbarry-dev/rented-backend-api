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
            return;
        }

        // Create super admin
        Admin::create([
            'name' => 'John Super',
            'email' => 'super@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'super',
            'is_active' => true,
        ]);

        // Create moderator admin
        Admin::create([
            'name' => 'Jane Moderator',
            'email' => 'mod@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'moderator',
            'is_active' => true,
        ]);
    }
}
