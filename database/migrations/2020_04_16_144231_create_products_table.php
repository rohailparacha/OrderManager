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
            $table->id();
            $table->string('image');
            $table->string('sc_id');
            $table->string('account');
            $table->string('asin');
            $table->string('upc');
            $table->string('title');
            $table->integer('totalSellers')->default(0);
            $table->double('lowestPrice',8,2)->default(0);
            $table->double('price',8,2)->default(0);
            $table->unsignedBigInteger('strategy_id');

            $table->foreign('strategy_id')->references('id')->on('strategies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
