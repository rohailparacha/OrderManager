<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbayStrategiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ebay_strategies', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('breakeven');
            $table->enum('type',[1,2]);
            $table->string('value');
            $table->boolean('isDefault')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ebay_strategies');
    }
}
