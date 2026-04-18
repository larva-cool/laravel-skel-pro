<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\System;

use App\Models\System\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

class AreaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Area::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->randomNumber(7),
            'name' => $this->faker->state(),
            'parent_id' => null,
            'area_code' => $this->faker->unique()->randomNumber(6),
            'child_ids' => '',
            'order' => $this->faker->randomNumber(),
        ];
    }

    /**
     * Create a child area state.
     *
     * @return \$this
     */
    public function child(int $parentId, int $level = 2)
    {
        return $this->state(function (array $attributes) use ($parentId) {
            $parent = Area::findOrFail($parentId);

            return [
                'parent_id' => $parentId,
            ];
        });
    }
}
