<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDraftSaleDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('draft_sale_details', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->integer('id', true);
			$table->date('date');
			$table->integer('draft_sale_id')->index('draft_sale_details_draft_sale_id');
			$table->integer('product_id')->index('draft_sale_details_product_id');
			$table->integer('product_variant_id')->nullable()->index('draft_sale_details_product_variant_id');
			$table->text('imei_number')->nullable();
			$table->float('price', 10, 0);
			$table->integer('sale_unit_id')->nullable()->index('draft_sale_details_sale_unit_id');
			$table->float('TaxNet', 10, 0)->nullable();
			$table->string('tax_method', 192)->nullable()->default('1');
			$table->float('discount', 10, 0)->nullable();
			$table->string('discount_method', 192)->nullable()->default('1');
			$table->float('total', 10, 0);
			$table->float('quantity', 10, 0);
			$table->timestamps(6);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('draft_sale_details');
	}

}
