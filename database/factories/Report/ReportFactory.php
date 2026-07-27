<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\Report;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Content\Comment;
use App\Models\Report\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
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
            'reportable_type' => 'comment',
            'reportable_id' => Comment::factory(),
            'reason' => ReportReason::SPAM->value,
            'content' => $this->faker->sentence(),
            'status' => ReportStatus::PENDING->value,
            'ip_address' => $this->faker->ipv4(),
        ];
    }
}
