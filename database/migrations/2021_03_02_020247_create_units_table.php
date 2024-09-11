<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 192);
            $table->string('short_name', 192);
            $table->boolean('is_active')->default(true);
            $table->char('operator', 192)->nullable()->default('*');
            $table->float('operator_value', 10, 0)->nullable()->default(1);

            $table->unsignedBigInteger('base_unit')->nullable();
            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('base_unit')->references('id')->on('units')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('units');
    }

}
