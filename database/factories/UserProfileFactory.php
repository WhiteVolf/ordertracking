<?php

namespace Database\Factories;

use App\Models\Nationality;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    protected $model = \App\Models\UserProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => $this->faker->name(),
            'phone_number' => $this->faker->phoneNumber(),
            'registration_address' => $this->faker->address(),
            'residential_address' => $this->faker->address(),
            'nationality_id' => Nationality::factory(),
            'tax_id_number' => $this->faker->numerify('##########'),
        ];
    }
}
