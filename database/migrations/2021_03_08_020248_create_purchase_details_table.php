<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_details', function(Blueprint $table)
		{
			$table->id();
			$table->decimal('cost', 16, 3);
			$table->decimal('tax_net', 16, 3)->default(0);
			$table->string('tax_method', 192)->default('1');
			$table->decimal('discount', 16, 3)->default(0);
			$table->string('discount_method', 192)->default('1');

			$table->decimal('total', 16, 3);
			$table->decimal('quantity', 16, 3);

            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->unsignedBigInteger('purchase_unit_id')->nullable();

			$table->timestamps(6);


            $table->foreign('purchase_unit_id')->references('id')->on('units')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('product_id', 'product_id')->references('id')->on('products')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('purchase_id', 'purchase_id')->references('id')->on('purchases')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('product_variant_id', 'purchase_product_variant_id')->references('id')->on('product_variants')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('purchase_details');
	}

}
