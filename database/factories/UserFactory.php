<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Support\Rbac\DefaultUserRoleAssigner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Фабрика модели User.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

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
        return ['name' => fake()->name(), 'email' => fake()->unique()->safeEmail(), 'email_verified_at' => now(),
            'account_status' => AccountStatus::Active, 'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10)];
    }

    /**
     * Indicate that the model's email address should be unverified.

     *
     * @return static
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => ['email_verified_at' => null,
            'account_status' => AccountStatus::Pending]);
    }

    /**
     * suspended.

     *
     * @return static
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => ['account_status' => AccountStatus::Suspended]);
    }

    /**
     * @return static
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            app(DefaultUserRoleAssigner::class)->assignIfMissing($user);
        });
    }
}
