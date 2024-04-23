<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDraftSaleDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('draft_sale_details', function(Blueprint $table)
		{
			$table->foreign('draft_sale_id', 'draft_sale_details_draft_sale_id')->references('id')->on('draft_sales')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('product_id', 'draft_sale_details_product_id')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('product_variant_id', 'draft_sale_details_product_variant_id')->references('id')->on('product_variants')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('sale_unit_id', 'draft_sale_details_sale_unit_id')->references('id')->on('units')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('draft_sale_details', function(Blueprint $table)
		{
			$table->dropForeign('draft_sale_details_draft_sale_id');
			$table->dropForeign('draft_sale_details_product_id');
			$table->dropForeign('draft_sale_details_product_variant_id');
			$table->dropForeign('draft_sale_details_sale_unit_id');
		});
	}

}
