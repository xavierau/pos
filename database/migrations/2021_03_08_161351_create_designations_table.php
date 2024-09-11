<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDesignationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('designations', function (Blueprint $table) {
            $table->id('id');
            $table->string('designation', 192);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('department_id');
            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments');
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
        Schema::drop('designations');
    }

}
