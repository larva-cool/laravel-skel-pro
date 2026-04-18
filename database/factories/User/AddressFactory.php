<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\User;

use App\Models\User;
use App\Models\User\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'country' => 'CN',
            'province' => $this->faker->state(),
            'city' => $this->faker->city(),
            'district' => $this->faker->streetName(),
            'address' => $this->faker->address(),
            'zipcode' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'is_default' => $this->faker->boolean(),
        ];
    }
}
