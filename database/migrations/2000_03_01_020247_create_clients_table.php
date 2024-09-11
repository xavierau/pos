<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clients', function(Blueprint $table)
		{
			$table->id('id');
			$table->string('name');
			$table->integer('code')->nullable();
			$table->string('email', 192)->nullable();
			$table->string('country')->nullable();
			$table->string('city')->nullable();
			$table->string('phone')->nullable();
			$table->string('address')->nullable();
			$table->string('tax_number')->nullable();
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
		Schema::drop('clients');
	}

}
