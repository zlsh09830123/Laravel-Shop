<?php

namespace Database\Factories;

use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'city' => $this->faker->city,
            'address' => $this->faker->streetAddress,
            'zip' => $this->faker->postcode,
            'contact_name' => $this->faker->name,
            'contact_phone' => $this->faker->phoneNumber,
        ];
    }
}