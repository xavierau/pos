<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHolidaysTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('holidays', function(Blueprint $table)
		{
			$table->id('id');
			$table->string('title', 192);
			$table->date('start_date');
			$table->date('end_date');
			$table->text('description')->nullable();

            $table->unsignedBigInteger('company_id');

			$table->timestamps(6);
			$table->softDeletes();

            $table->foreign('company_id', 'holidays_company_id')->references('id')->on('companies');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('holidays');
	}

}
