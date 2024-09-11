<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReturnsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();

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

            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sale_unit_id')->nullable();
            $table->unsignedBigInteger('sale_id')->nullable();

            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('sale_unit_id')->references('id')->on('units');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sale_returns');
    }

}
