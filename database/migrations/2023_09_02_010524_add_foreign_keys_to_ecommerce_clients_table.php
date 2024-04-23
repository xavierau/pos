<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEcommerceClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ecommerce_clients', function(Blueprint $table)
		{
			$table->foreign('client_id', 'ecommerce_clients_client_id')->references('id')->on('clients')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ecommerce_clients', function(Blueprint $table)
		{
			$table->dropForeign('ecommerce_clients_client_id');
		});
	}

}
