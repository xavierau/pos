<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountedPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounted_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discountable_id');
            $table->string('discountable_type');
            $table->decimal('discounted_price', 8, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
            $table->index(['discountable_id', 'discountable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discounted_prices');
    }
}
