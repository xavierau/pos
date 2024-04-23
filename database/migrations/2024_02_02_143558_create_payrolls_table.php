<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payrolls', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->integer('id', true);
			$table->integer('user_id')->index('payrolls_user_id');
			$table->string('Ref', 192);
			$table->date('date');
			$table->integer('employee_id')->index('payrolls_employee_id');
			$table->integer('account_id')->nullable()->index('payrolls_account_id');
			$table->float('amount', 10, 0);
			$table->string('payment_method', 192);
			$table->string('payment_status', 192);
			$table->timestamps(6);
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payrolls');
	}

}
