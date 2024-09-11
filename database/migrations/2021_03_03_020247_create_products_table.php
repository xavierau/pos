<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('id');
            $table->string('code', 192);
            $table->string('type_barcode', 192);
            $table->string('name', 192);
            $table->float('cost', 10, 0);
            $table->float('price', 10, 0);

            $table->float('tax_net', 10, 0)->nullable()->default(0);
            $table->string('tax_method', 192)->nullable()->default('1');
            $table->text('image')->nullable();
            $table->text('note')->nullable();
            $table->float('stock_alert', 10, 0)->nullable()->default(0);
            $table->boolean('is_active')->nullable()->default(true);
            $table->boolean('is_imei')->default(false);
            $table->boolean('is_variant')->default(false);

            $table->unsignedBigInteger('category_id')->index('category_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('unit_sale_id')->nullable();
            $table->unsignedBigInteger('unit_purchase_id')->nullable();

            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('unit_sale_id')->references('id')->on('units');
            $table->foreign('unit_purchase_id')->references('id')->on('units');

        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('products');
    }

}
