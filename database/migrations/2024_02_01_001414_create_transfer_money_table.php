<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferMoneyTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_money', function (Blueprint $table) {
            $table->id('id');

            $table->date('date');
            $table->float('amount', 10, 0);

            $table->unsignedBigInteger('from_account_id');
            $table->unsignedBigInteger('to_account_id');

            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('from_account_id')->references('id')->on('accounts');
            $table->foreign('to_account_id')->references('id')->on('accounts');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transfer_money');
    }

}
