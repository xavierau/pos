<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeExperiencesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employee_experiences', function(Blueprint $table)
		{
			$table->id('id', true);
			$table->string('title', 192);
			$table->string('company_name', 192);
			$table->string('location', 192)->nullable();
			$table->string('employment_type', 192);
			$table->date('start_date');
			$table->date('end_date');
			$table->text('description')->nullable();
            $table->unsignedBigInteger('employee_id');
			$table->timestamps(6);
			$table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees');

		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('employee_experiences');
	}

}
