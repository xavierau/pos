<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseReturnDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_return_details', function (Blueprint $table) {
            $table->id();
            $table->decimal('cost', 16, 3);
            $table->decimal('tax_net', 16, 3)->default(0);
            $table->string('tax_method', 192)->default('1');
            $table->decimal('discount', 16, 3)->default(0);
            $table->string('discount_method', 192)->default('1');
            $table->decimal('total', 16, 3);
            $table->decimal('quantity', 16, 3);

            $table->unsignedBigInteger('purchase_return_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->unsignedBigInteger('purchase_unit_id')->nullable();


            $table->timestamps(6);
            $table->softDeletes();


            $table->foreign('purchase_unit_id')->references('id')->on('units');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('purchase_return_id')->references('id')->on('purchase_returns');
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('purchase_return_details');
    }

}
