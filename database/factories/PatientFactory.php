<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "surname"=>$this->faker->lastName(),
            "name"=>$this->faker->firstName(),
            // Middle Name optional
            "middleName"=> $this->faker->boolean(20) ? $this->faker->firstName() : null,
            "birthday"=>$this->faker->date(),
            "gender"=> $this->faker->boolean(5) ? "N" : ($this->faker->boolean(65)?"F":"M"),
            "race"=> $this->faker->boolean(5) ? "X" : ($this->faker->boolean(10)?"O":$this->faker->randomElement(['C','B','I','A','P'])),
            "address"=> $this->faker->streetAddress(),
            "city"=>$this->faker->city(),
            "state"=>$this->faker->stateAbbr(),
            "zip"=>$this->faker->postcode(),
            "phoneNumber"=>$this->faker->phoneNumber(),
            "ethnic"=>$this->faker->randomElement(['H','N','U'])
        ];
    }
}
