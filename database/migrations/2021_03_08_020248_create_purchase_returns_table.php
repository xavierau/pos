<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseReturnsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id('id');

            $table->date('date');
            $table->string('ref', 192);

            $table->float('tax_rate', 10, 0)->nullable()->default(0);
            $table->float('tax_net', 10, 0)->nullable()->default(0);
            $table->float('discount', 10, 0)->nullable()->default(0);
            $table->float('shipping', 10, 0)->nullable()->default(0);
            $table->float('grand_total', 10, 0);
            $table->float('paid_amount', 10, 0)->default(0);
            $table->string('payment_status', 192);
            $table->string('status', 192);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('purchase_id')->nullable();

            $table->timestamps(6);
            $table->softDeletes();


            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('provider_id')->references('id')->on('providers');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('purchase_returns');
    }

}
