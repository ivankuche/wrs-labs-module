<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Insurance>
 */
class InsuranceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

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
        $identificationNumber= $this->optionalField($this->faker->randomNumber(5));
        $payerCode= $this->optionalField($this->faker->randomElement(['05',$this->faker->stateAbbr(),$this->faker->randomNumber(5),$this->faker->word()]));
        $insuredPatientGroupNumber= $this->optionalField($this->faker->randomNumber(6));
        $insuredGroupEmployerName= $this->optionalField($this->faker->company());
        $planType= $this->optionalField($this->faker->randomElement(['MD','MC','HM','PI']));
        $insuredSurname= $this->optionalField($this->faker->lastName());
        $insuredName= $this->optionalField($this->faker->firstName());
        $insuredMiddleName= $this->optionalField($this->faker->firstName());
        $insuredAddress= $this->optionalField($this->faker->streetAddress());
        $insuredCity= $this->optionalField($this->faker->city());
        $insuredState= $this->optionalField($this->faker->stateAbbr());
        $insuredZip= $this->optionalField($this->faker->postcode());
        $workerCompensation= $this->faker->randomElement(["N","Y"]);

        return [
            "identificationNumber"=>$identificationNumber,
            "payerCode"=>$payerCode,
            "insuranceCompanyName"=>$this->faker->company(),
            "insuranceCompanyAddress"=>$this->faker->streetAddress(),
            "insuranceCompanyCity"=>$this->faker->city(),
            "insuranceCompanyState"=>$this->faker->stateAbbr(),
            "insuranceCompanyZip"=>$this->faker->postcode(),
            "insuredPatientGroupNumber"=>$insuredPatientGroupNumber,
            "insuredGroupEmployerName"=>$insuredGroupEmployerName,
            "planType"=>$planType,
            "insuredSurname"=>$insuredSurname,
            "insuredName"=>$insuredName,
            "insuredMiddleName"=>$insuredMiddleName,
            "insuredRelationshipWithPatient"=>$this->faker->randomElement([1,2,3]), // Self, Spouse, Other
            "insuredAddress"=>$insuredAddress,
            "insuredCity"=>$insuredCity,
            "insuredState"=>$insuredState,
            "insuredZip"=>$insuredZip,
            "workerCompensation"=>$workerCompensation,
            "policyNumber"=>$this->faker->randomNumber(8)
        ];
    }
}
