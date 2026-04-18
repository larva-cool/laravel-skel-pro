<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\System;

use App\Models\System\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition()
    {
        return [
            'key' => $this->faker->unique()->word.'.'.$this->faker->unique()->word,
            'value' => $this->faker->randomElement([
                $this->faker->word,
                $this->faker->numberBetween(1, 1000),
                $this->faker->boolean,
                $this->faker->randomFloat(2, 0, 1000),
            ]),
            'cast_type' => $this->faker->randomElement(['string', 'int', 'bool', 'float']),
            'description' => $this->faker->sentence,
        ];
    }
}
