<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentPurchaseReturnsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment_purchase_returns', function(Blueprint $table)
		{
			$table->id();

			$table->date('date');
			$table->string('ref', 192);

			$table->float('amount', 10, 0);
            $table->float('change', 10, 0)->default(0);
			$table->string('type', 191);
			$table->text('notes')->nullable();
            $table->unsignedBigInteger('purchase_return_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('account_id')->nullable();
			$table->timestamps(6);
			$table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('purchase_return_id')->references('id')->on('purchase_returns');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payment_purchase_returns');
	}

}
