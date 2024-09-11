<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_details', function (Blueprint $table) {
            $table->id('id');

            $table->float('cost', 10, 0);
            $table->float('TaxNet', 10, 0)->nullable();
            $table->string('tax_method', 192)->nullable()->default('1');
            $table->float('discount', 10, 0)->nullable();
            $table->string('discount_method', 192)->nullable()->default('1');
            $table->float('quantity', 10, 0);
            $table->float('total', 10, 0);

            $table->unsignedBigInteger('transfer_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->unsignedBigInteger('purchase_unit_id')->nullable();
            $table->timestamps(6);

            $table->foreign('purchase_unit_id')->references('id')->on('units');
            $table->foreign('product_id',)->references('id')->on('products');
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
            $table->foreign('transfer_id')->references('id')->on('transfers');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transfer_details');
    }

}
