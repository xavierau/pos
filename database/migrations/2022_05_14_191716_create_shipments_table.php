<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id('id');

            $table->timestamp('date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('ref', 192);

            $table->string('delivered_to', 192)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('status', 192);
            $table->text('shipping_details')->nullable();

            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('user_id');

            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('sale_id')->references('id')->on('sales');
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
        Schema::drop('shipments');
    }

}
