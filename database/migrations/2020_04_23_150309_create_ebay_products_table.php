<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbayProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ebay_products', function (Blueprint $table) {
            $table->id();
            $table->dateTime('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('sku');
            $table->string('name',200);
            $table->enum('productIdType',['UPC','EAN','ISBN','GTIN']);
            $table->string('productId');
            $table->string('description',4000);
            $table->string('brand',60);
            $table->string('primaryImg');
            $table->string('secondaryImg')->nullable();
            $table->double('ebayPrice',8,2);
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('strategy_id');
            $table->unsignedBigInteger('account_id');
            $table->double('price',8,2);
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('strategy_id')->references('id')->on('ebay_strategies');
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
        Schema::dropIfExists('ebay_products');
    }
}
