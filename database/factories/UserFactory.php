<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
        $username = fake()->unique()->userName();
        $username = str_replace(' ', '', $username);

        return [
            'username' => $username,
            'name' => fake()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => fake()->unique()->safeEmail(),
        ]);
    }

    /**
     * Indicate that the model's phone number should be unverified.
     */
    public function phone(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => fake()->unique()->phoneNumber(),
        ]);
    }

    /**
     * Indicate that the model's empty password should be unverified.
     */
    public function empty_password(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
        ]);
    }
}
