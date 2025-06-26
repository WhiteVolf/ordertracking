<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NationalityFactory extends Factory
{
    protected $model = \App\Models\Nationality::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->country(),
        ];
    }
}
