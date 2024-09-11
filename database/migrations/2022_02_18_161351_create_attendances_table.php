<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('attendances', function(Blueprint $table)
		{
			$table->id('id');

			$table->date('date');
			$table->string('clock_in', 191);
			$table->string('clock_in_ip', 45);
			$table->string('clock_out', 191);
			$table->string('clock_out_ip', 191);
			$table->boolean('clock_in_out');
			$table->string('depart_early', 191)->default('00:00');
			$table->string('late_time', 191)->default('00:00');
			$table->string('overtime', 191)->default('00:00');
			$table->string('total_work', 191)->default('00:00');
			$table->string('total_rest', 191)->default('00:00');
			$table->string('status', 191)->default('present');

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('employee_id');

			$table->timestamps(6);
			$table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('user_id')->references('id')->on('users');
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
		Schema::drop('attendances');
	}

}
