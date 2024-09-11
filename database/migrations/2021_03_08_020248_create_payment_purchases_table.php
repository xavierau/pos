<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentPurchasesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment_purchases', function(Blueprint $table)
		{
			$table->id('id');

			$table->date('date');
			$table->string('ref', 192);
			$table->float('amount', 10, 0);
            $table->float('change', 10, 0)->default(0);
			$table->string('type', 192);
			$table->text('notes')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('account_id')->nullable();

			$table->timestamps(6);
			$table->softDeletes();

            $table->foreign('purchase_id' )->references('id')->on('purchases');
            $table->foreign('user_id')->references('id')->on('users');
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
		Schema::drop('payment_purchases');
	}

}
