<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficeShiftsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('office_shifts', function (Blueprint $table) {
            $table->id('id');

            $table->string('name', 191);
            $table->string('monday_in', 191)->nullable();
            $table->string('monday_out', 191)->nullable();
            $table->string('tuesday_in', 191)->nullable();
            $table->string('tuesday_out', 191)->nullable();
            $table->string('wednesday_in', 191)->nullable();
            $table->string('wednesday_out', 191)->nullable();
            $table->string('thursday_in', 191)->nullable();
            $table->string('thursday_out', 191)->nullable();
            $table->string('friday_in', 191)->nullable();
            $table->string('friday_out', 191)->nullable();
            $table->string('saturday_in', 191)->nullable();
            $table->string('saturday_out', 191)->nullable();
            $table->string('sunday_in', 191)->nullable();
            $table->string('sunday_out', 191)->nullable();

            $table->unsignedBigInteger('company_id');

            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');

        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('office_shifts');
    }

}
