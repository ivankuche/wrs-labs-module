<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->string('identificationNumber')->nullable();
            $table->string('payerCode')->nullable();
            $table->string('insuranceCompanyName');
            $table->string('insuranceCompanyAddress');
            $table->string('insuranceCompanyCity');
            $table->char('insuranceCompanyState',2);
            $table->string('insuranceCompanyZip');
            $table->string('insuredPatientGroupNumber')->nullable();
            $table->string('insuredGroupEmployerName')->nullable();
            $table->char('planType',2)->nullable(); // Should be deprecated and replaced with the new billing methods
            $table->string('insuredSurname')->nullable();
            $table->string('insuredName')->nullable();
            $table->string('insuredMiddleName')->nullable();
            $table->char('insuredRelationshipWithPatient',1);
            $table->string('insuredAddress')->nullable();
            $table->string('insuredCity')->nullable();
            $table->char('insuredState',2)->nullable();
            $table->string('insuredZip')->nullable();
            $table->char('workerCompensation',1);
            $table->string('policyNumber');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('insurances');
    }
};
