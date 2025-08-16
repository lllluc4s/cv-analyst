<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'),
            'cnpj' => $this->faker->numerify('##.###.###/####-##'),
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country' => 'Brasil',
            'website' => $this->faker->optional()->url,
            'description' => $this->faker->optional()->text,
            'slug' => $this->faker->unique()->slug,
            'email_verified_at' => now(),
        ];
    }
}
