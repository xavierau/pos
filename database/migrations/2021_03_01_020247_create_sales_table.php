<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->dateTime('date');
            $table->string('ref', 192);
            $table->boolean('is_pos')->default(false);
            $table->decimal('tax_rate', 16, 2)->default(0);
            $table->decimal('tax_net', 16, 2)->default(0);
            $table->decimal('discount', 16, 2)->default(0);
            $table->decimal('shipping', 16, 2)->default(0);
            $table->decimal('grand_total', 16, 2)->default(0);
            $table->decimal('paid_amount', 16, 2)->default(0);
            $table->string('payment_status', 192);
            $table->string('status');
            $table->string('shipping_status')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('warehouse_id');

            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sales');
    }

}
