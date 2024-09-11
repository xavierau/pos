<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->date('date');

            $table->decimal('price', 16,2)->default(0.0);
            $table->decimal('tax_net', 16,2)->default(0.0);
            $table->string('tax_method', 192)->default('1');
            $table->decimal('discount', 16,2)->nullable();
            $table->string('discount_method', 192)->default('1');
            $table->decimal('quantity', 16,2)->default(0.0);;

            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->unsignedBigInteger('sale_unit_id')->nullable();


            $table->timestamps(6);

            $table->foreign('sale_id')->references('id')->on('sales')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('sale_unit_id')->references('id')->on('units')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sale_details');
    }

}
