<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountedPriceSaleDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounted_price_sale_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('discounted_price_id');
            $table->unsignedBigInteger('sale_detail_id');
            $table->foreign('discounted_price_id')->references('id')->on('discounted_prices');
            $table->foreign('sale_detail_id')->references('id')->on('sale_details');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discounted_price_sale');
    }
}
