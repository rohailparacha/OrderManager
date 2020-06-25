<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();    
            $table->string('orderId')->unique();
            $table->unsignedBigInteger('account_id');
            $table->date('date'); 
            $table->text('marketplace');
            $table->text('storeName');
            $table->text('sellOrderId');
            $table->text('buyerName');
            $table->integer('quantity');
            $table->float('totalAmount');
            $table->text('address1')->nullable();
            $table->text('address2')->nullable();
            $table->text('address3')->nullable();
            $table->text('city')->nullable();
            $table->text('state')->nullable();
            $table->text('country')->nullable();
            $table->text('postalCode')->nullable();
            $table->text('phone')->nullable();
            $table->float('poTotalAmount')->nullable();
            $table->text('poNumber')->nullable();
            $table->text('uid')->nullable();
            $table->text('carrierName')->nullable();
            $table->text('trackingNumber')->nullable();
            $table->text('newTrackingNumber')->nullable();
            $table->boolean('converted')->default(false);
            $table->integer('assigned')->default(0);
            $table->enum('status', ['unshipped','pending','shipped','cancelled','processing']);
            $table->enum('flag', ['0','1','2','3','4','5','6']);
            $table->foreign('account_id')->references('id')->on('accounts');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
