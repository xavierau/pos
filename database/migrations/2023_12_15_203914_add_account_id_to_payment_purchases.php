<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToPaymentPurchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_purchases', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('account_id')->nullable()->after('purchase_id');
            $table->foreign('account_id', 'payment_purchases_account_id')->references('id')->on('accounts')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_purchases', function (Blueprint $table) {
            $table->dropForeign('payment_purchases_account_id');
        });
    }
}
