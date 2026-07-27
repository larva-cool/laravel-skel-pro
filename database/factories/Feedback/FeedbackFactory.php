<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\Feedback;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Models\Feedback\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => FeedbackType::SUGGESTION->value,
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(),
            'contact' => $this->faker->safeEmail(),
            'status' => FeedbackStatus::PENDING->value,
            'ip_address' => $this->faker->ipv4(),
        ];
    }
}
