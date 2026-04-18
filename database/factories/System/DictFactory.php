<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\System;

use App\Models\System\Dict;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dict>
 */
class DictFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'code' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement([0, 1]),
            'order' => fake()->numberBetween(0, 100),
        ];
    }
}
