<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\System;

use Illuminate\Database\Eloquent\Factories\Factory;

class PhoneCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => $this->faker->unique()->phoneNumber,
            'code' => $this->faker->numerify('######'),
            'send_at' => now(),
        ];
    }
}
