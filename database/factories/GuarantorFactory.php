<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guarantor>
 */
class GuarantorFactory extends Factory
{

    private function optionalField($values)
    {
        $return= null;
        if ($this->faker->boolean())
            $return= $values;

        return $return;
    }

    public function definition()
    {

        // Optional fields
        $middleName= $this->optionalField($this->faker->firstName());
        $employerName= $this->optionalField($this->faker->name());




        return [
            "surname"=>$this->faker->lastName(),
            "name"=>$this->faker->firstName(),
            "middleName"=>$middleName,
            "address"=>$this->faker->streetAddress(),
            "city"=>$this->faker->city(),
            "state"=>$this->faker->stateAbbr(),
            "zip"=>$this->faker->postcode(),
            "phone"=>$this->faker->phoneNumber(),
            // 1: Self, 2: Spouse, 3: Other
            "guarantorRelationshipWithPatient"=>$this->faker->randomElement(['1','2','3']),
            "employerName"=>$employerName
        ];
    }
}
