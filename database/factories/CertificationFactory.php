<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\CertificationStatus;
use App\Enum\CertificationType;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Certification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'certifiable_type' => User::class,
            'certifiable_id' => User::factory(),
            'type' => fake()->randomElement([CertificationType::PERSONAL, CertificationType::ENTERPRISE]),
            'real_name' => fake()->name(),
            'id_card_no' => fake()->numerify('##################'),
            'id_card_front' => fake()->imageUrl(),
            'id_card_back' => fake()->imageUrl(),
            'id_card_in_hand' => fake()->imageUrl(),
            'license' => fake()->imageUrl(),
            'contact_person' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'contact_email' => fake()->email(),
            'status' => CertificationStatus::UNSUBMITTED,
            'failed_reason' => null,
            'verified_at' => null,
            'submitted_at' => null,
            'updated_at' => now(),
        ];
    }

    /**
     * 个人认证
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CertificationType::PERSONAL,
        ]);
    }

    /**
     * 企业认证
     */
    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CertificationType::ENTERPRISE,
        ]);
    }

    /**
     * 待审核
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CertificationStatus::PENDING,
            'submitted_at' => now(),
        ]);
    }

    /**
     * 已通过
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CertificationStatus::APPROVED,
            'verified_at' => now(),
            'submitted_at' => now()->subDay(),
        ]);
    }

    /**
     * 已拒绝
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CertificationStatus::REJECTED,
            'failed_reason' => fake()->sentence(),
            'submitted_at' => now()->subDay(),
        ]);
    }
}
