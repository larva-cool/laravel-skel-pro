<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 用户模型工厂
 *
 * @extends Factory<User>
 *
 * @author Tongle Xu <xutongle@msn.com>
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
            'username' => fake()->userName(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+8613#########'),
            'avatar' => null,
            'status' => UserStatus::STATUS_ACTIVE,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is frozen.
     */
    public function frozen(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::STATUS_FROZEN,
        ]);
    }

    /**
     * Indicate that the user is not active.
     */
    public function notActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::STATUS_NOT_ACTIVE,
        ]);
    }
}
