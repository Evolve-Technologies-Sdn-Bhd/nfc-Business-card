<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'plan' => 'free',
            'subscription_plan' => 'basic',
            'subscription_active' => false,
            'is_admin' => false,
            'is_new_user' => false,
            'total_account_slots' => 0,
            'total_card_quota' => 0,
            'business_role' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * A Business account owner (tenant root).
     */
    public function businessOwner(int $slots = 10): static
    {
        return $this->state(fn () => [
            'subscription_plan' => 'business',
            'subscription_active' => true,
            'parent_business_id' => null,
            'total_account_slots' => $slots,
            'total_card_quota' => $slots,
            'business_role' => null,
        ]);
    }

    /**
     * An employee under a Business account, with a delegated business role.
     */
    public function businessEmployee(User $owner, string $role = User::BUSINESS_ROLE_MEMBER): static
    {
        return $this->state(fn () => [
            'parent_business_id' => $owner->id,
            'subscription_plan' => 'business',
            'subscription_active' => true,
            'business_role' => $role,
        ]);
    }
}
