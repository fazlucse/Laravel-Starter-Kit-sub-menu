<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = \App\Models\Person::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'designation' => $this->faker->jobTitle(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
            'present_address' => $this->faker->address(),
            'education' => $this->faker->sentence(3),
            'section' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'material_status' => $this->faker->randomElement(['Single', 'Married']),
            'father_name' => $this->faker->name('male'),
            'mother_name' => $this->faker->name('female'),
            'company' => $this->faker->company(),
            'nationality' => $this->faker->country(),
            'national_id' => $this->faker->unique()->numerify('##########'),
            'tin' => $this->faker->unique()->numerify('##########'),
            'photo' => null,
            'status_photo' => null,
        ];
    }
}
