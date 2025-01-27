<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('username', 192);
            $table->string('email', 192);
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('phone', 192);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_all_warehouses')->default(false);
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
        Schema::drop('users');
    }

}
