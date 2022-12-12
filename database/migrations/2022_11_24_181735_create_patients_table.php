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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('surname');
            $table->string('name');
            $table->string('middleName')->nullable();
            $table->date('birthday');
            $table->char('gender',1);
            $table->char('race',1);
            $table->string('address');
            $table->string('city');
            $table->char('state',2);
            $table->string('zip');
            $table->string('phoneNumber');
            $table->char('ethnic',1);
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
        Schema::dropIfExists('patients');
    }
};
