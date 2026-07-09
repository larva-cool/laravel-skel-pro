<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\System;

use App\Enums\StatusSwitch;
use App\Models\System\Agreement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agreement>
 */
class AgreementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['user', 'privacy', 'service']),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'status' => StatusSwitch::ENABLED->value,
            'order' => fake()->numberBetween(0, 100),
            'admin_id' => 1,
        ];
    }

    /**
     * Indicate that the agreement is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusSwitch::ENABLED->value,
        ]);
    }

    /**
     * Indicate that the agreement is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusSwitch::DISABLED->value,
        ]);
    }

    /**
     * Indicate that the agreement is for user.
     */
    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'user',
        ]);
    }

    /**
     * Indicate that the agreement is for privacy.
     */
    public function forPrivacy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'privacy',
        ]);
    }
}
