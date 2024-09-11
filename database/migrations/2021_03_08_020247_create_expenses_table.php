<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expenses', function(Blueprint $table)
		{
			$table->id('id');
			$table->date('date');
			$table->string('ref', 192);

			$table->string('details', 192);
			$table->float('amount', 10, 0);

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('expense_category_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('account_id')->nullable();
			$table->timestamps(6);
			$table->softDeletes();

            $table->foreign('expense_category_id', 'expense_category_id')->references('id')->on('expense_categories');
            $table->foreign('user_id', 'expense_user_id')->references('id')->on('users');
            $table->foreign('warehouse_id', 'expense_warehouse_id')->references('id')->on('warehouses');
            $table->foreign('account_id')->references('id')->on('accounts');

        });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('expenses');
	}

}
