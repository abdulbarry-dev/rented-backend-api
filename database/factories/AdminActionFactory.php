<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\AdminAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminAction>
 */
class AdminActionFactory extends Factory
{
    protected $model = AdminAction::class;

    /**
     * Common admin actions for realistic logs
     */
    private const ACTIONS = [
        'login',
        'logout',
        'logout_all',
        'admin_approved',
        'admin_rejected',
        'admin_banned',
        'admin_unbanned',
        'admin_deleted',
        'product_reviewed',
        'user_verification_reviewed',
        'user_activated',
        'user_deactivated',
    ];

    /**
     * Common target types
     */
    private const TARGET_TYPES = [
        'system',
        Admin::class,
        \App\Models\User::class,
        \App\Models\Product::class,
        \App\Models\UserVerification::class,
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $action = fake()->randomElement(self::ACTIONS);
        $targetType = $this->getTargetTypeForAction($action);

        return [
            'admin_id' => Admin::factory(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetType === 'system' ? null : fake()->numberBetween(1, 100),
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Get appropriate target type based on action
     */
    private function getTargetTypeForAction(string $action): string
    {
        return match($action) {
            'login', 'logout', 'logout_all' => 'system',
            'admin_approved', 'admin_rejected', 'admin_banned', 'admin_unbanned', 'admin_deleted' => Admin::class,
            'user_activated', 'user_deactivated' => \App\Models\User::class,
            'product_reviewed' => \App\Models\Product::class,
            'user_verification_reviewed' => \App\Models\UserVerification::class,
            default => 'system',
        };
    }

    /**
     * Action by specific admin
     */
    public function byAdmin(Admin|int $admin): static
    {
        $adminId = $admin instanceof Admin ? $admin->id : $admin;
        
        return $this->state(fn (array $attributes) => [
            'admin_id' => $adminId,
        ]);
    }

    /**
     * Specific action type
     */
    public function action(string $action): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => $action,
            'target_type' => $this->getTargetTypeForAction($action),
        ]);
    }

    /**
     * System action (no target)
     */
    public function systemAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'system',
            'target_id' => null,
        ]);
    }

    /**
     * Login action
     */
    public function login(): static
    {
        return $this->action('login')->systemAction();
    }

    /**
     * Logout action
     */
    public function logout(): static
    {
        return $this->action('logout')->systemAction();
    }

    /**
     * Recent action
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
