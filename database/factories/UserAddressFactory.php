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
        $city_district = $this->faker->city;
        $city = mb_substr($city_district, 0, 3);
        $district = mb_substr($city_district, 3);

        return [
            'city' => $city,
            'district' => $district,
            'address' => $this->faker->streetAddress,
            'zip_code' => $this->faker->postcode,
            'contact_name' => $this->faker->name,
            'contact_phone' => $this->faker->phoneNumber,
        ];
    }
}
