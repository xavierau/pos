<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('quotations', function(Blueprint $table)
		{
			$table->id('id');
			$table->date('date');
			$table->string('Ref', 192);
			$table->float('tax_rate', 10, 0)->nullable()->default(0);
			$table->float('TaxNet', 10, 0)->nullable()->default(0);
			$table->float('discount', 10, 0)->nullable()->default(0);
			$table->float('shipping', 10, 0)->nullable()->default(0);
			$table->float('GrandTotal', 10, 0);
			$table->string('status', 192);
			$table->text('notes')->nullable();

            $table->unsignedBigInteger('user_id')->index('user_id_quotation');
            $table->unsignedBigInteger('client_id')->index('client_id_quotation');
            $table->unsignedBigInteger('warehouse_id')->index('warehouse_id_quotation');

			$table->timestamps(6);
			$table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('quotations');
	}

}
