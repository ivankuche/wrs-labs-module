<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    private function optionalField($values)
    {
        $return= null;
        if ($this->faker->boolean())
            $return= $values;

        return $return;
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $applicationID= $this->optionalField($this->faker->randomNumber(6));
        $orderingProviderID= $this->optionalField($this->faker->randomNumber(4));
        $orderingProviderSurname= $this->optionalField($this->faker->lastName());
        $orderingProviderName= $this->optionalField($this->faker->firstName());
        $sourceTable= $this->optionalField($this->faker->randomElement(['N','L','U','P']));


        return [
            'uniqueAccession'=>$this->faker->randomNumber(7,true),
            'applicationID'=>$applicationID,
            'orderingProviderID'=>$orderingProviderID,
            'orderingProviderSurname'=>$orderingProviderSurname,
            'orderingProviderName'=>$orderingProviderName,
            'sourceTable'=>$sourceTable
            //
        ];
    }
}
