<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeAccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employee_accounts', function(Blueprint $table)
		{
			$table->id('id', true);

			$table->string('bank_name', 192);
			$table->string('bank_branch', 192);
			$table->string('account_no', 192);
			$table->text('note')->nullable();

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
		Schema::drop('employee_accounts');
	}

}
