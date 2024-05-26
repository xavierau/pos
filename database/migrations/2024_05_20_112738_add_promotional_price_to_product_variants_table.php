<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromotionalPriceToProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->double('promotional_price')->nullable()->after('price');
            $table->date('promotional_start_date')->nullable()->after('promotional_price');
            $table->date('promotional_end_date')->nullable()->after('promotional_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('promotional_price', 'promotional_start_date', 'promotional_end_date');
        });
    }
}
