<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdjustmentDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('adjustment_details', function(Blueprint $table)
		{
			$table->id('id', true);
			$table->float('quantity', 10, 0);
			$table->string('type', 192);

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('adjustment_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();

			$table->timestamps(6);

            $table->foreign('adjustment_id' )->references('id')->on('adjustments')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('adjustment_details');
	}

}
