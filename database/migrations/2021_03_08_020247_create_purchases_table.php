<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->string('ref', 192);
            $table->date('date');
            $table->decimal('tax_rate', 10, 0)->default(0);
            $table->decimal('tax_net', 10, 0)->default(0);
            $table->decimal('discount', 10, 0)->default(0);
            $table->decimal('shipping', 10, 0)->default(0);
            $table->decimal('grand_total', 10, 0)->default(0);
            $table->decimal('paid_amount', 10, 0)->default(0);
            $table->string('status');
            $table->string('payment_status', 192);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('warehouse_id');

            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('provider_id')->references('id')->on('providers');
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
        Schema::drop('purchases');
    }

}
