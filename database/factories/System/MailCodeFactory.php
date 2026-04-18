<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\System;

use App\Models\System\MailCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailCode>
 */
class MailCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'code' => $this->faker->numerify('######'),
            'send_at' => now(),
        ];
    }
}
