<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalmartProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('walmart_products', function (Blueprint $table) {
            $table->id();
            $table->dateTime('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));            
            $table->string('name',200);
            $table->enum('productIdType',['UPC','ISBN10','ISBN13']);
            $table->string('productId');            
            $table->string('seller',60);
            $table->string('link');
            $table->string('image')->nullable();            
            $table->double('price',8,2);            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('walmart_products');
    }
}
