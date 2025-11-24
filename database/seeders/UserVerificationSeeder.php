<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Database\Seeder;

class UserVerificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding user verifications...');

        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Skipping user verification seeding.');
            return;
        }

        // Get a super admin for reviews
        $superAdmin = Admin::where('role', 'super')->where('status', 'active')->first();

        if (!$superAdmin) {
            $this->command->warn('No active super admin found. Some verifications will not have reviewer.');
        }

        $totalVerifications = 0;

        // Add verifications to users (80% of users have verification)
        foreach ($users as $user) {
            if (fake()->boolean(80)) {
                // Determine verification status (60% verified, 30% pending, 10% rejected)
                $status = fake()->randomElement([
                    'verified', 'verified', 'verified', 'verified', 'verified', 'verified',
                    'pending', 'pending', 'pending',
                    'rejected',
                ]);

                $verification = UserVerification::factory()
                    ->forUser($user)
                    ->nationalId()
                    ->{$status}()
                    ->create();

                // If reviewed, assign the reviewer
                if (in_array($status, ['verified', 'rejected']) && $superAdmin) {
                    $verification->update(['reviewed_by' => $superAdmin->id]);
                }

                $totalVerifications++;
            }
        }

        // Create specific test scenarios

        // 1. User with verified national ID
        $fullyVerifiedUser = $users->random();
        UserVerification::where('user_id', $fullyVerifiedUser->id)->delete();

        UserVerification::factory()
            ->forUser($fullyVerifiedUser)
            ->nationalId()
            ->verified()
            ->create(['reviewed_by' => $superAdmin?->id]);

        // 2. User with pending verification
        $pendingUser = $users->random();
        UserVerification::where('user_id', $pendingUser->id)->delete();

        UserVerification::factory()
            ->forUser($pendingUser)
            ->nationalId()
            ->pending()
            ->create();

        // 3. User with rejected verification
        $rejectedUser = $users->random();
        UserVerification::where('user_id', $rejectedUser->id)->delete();

        UserVerification::factory()
            ->forUser($rejectedUser)
            ->nationalId()
            ->rejected()
            ->create(['reviewed_by' => $superAdmin?->id]);

        $totalVerifications = UserVerification::count();
        $this->command->info("User verification seeding completed! Total verifications: {$totalVerifications}");
        $this->command->info("  - Verified: " . UserVerification::where('verification_status', 'verified')->count());
        $this->command->info("  - Pending: " . UserVerification::where('verification_status', 'pending')->count());
        $this->command->info("  - Rejected: " . UserVerification::where('verification_status', 'rejected')->count());
    }
}
