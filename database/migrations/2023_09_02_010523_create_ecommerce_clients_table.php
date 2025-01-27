<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcommerceClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ecommerce_clients', function(Blueprint $table)
		{
			$table->id('id');

			$table->string('username', 192);
			$table->string('email', 192);
			$table->dateTime('email_verified_at')->nullable();
			$table->string('password', 191);
			$table->string('remember_token', 100)->nullable();
			$table->boolean('status')->default(1);
            $table->unsignedBigInteger('client_id');
			$table->timestamps(6);
			$table->softDeletes();

            $table->foreign('client_id')->references('id')->on('clients');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ecommerce_clients');
	}

}
