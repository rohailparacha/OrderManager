<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
                       
            $table->integer('minAmount')->default(0);
            $table->integer('maxAmount')->default(1000);
            $table->boolean('amountCheck')->default(false);
            $table->boolean('listCheck')->default(false);
            $table->boolean('sidebarCheck')->default(true);            
            $table->integer('minQty')->default(0);
            $table->integer('maxQty')->default(1000);
            $table->boolean('quantityRangeCheck')->default(false);
            
            $table->string('stores')->nullable();
            $table->boolean('storesCheck')->default(false);

            $table->integer('discount')->default(0);

            $table->integer('maxPrice')->default(0);

            $table->integer('maxDailyOrder')->default(0);
            $table->boolean('dailyOrderCheck')->default(false);

            $table->integer('maxDailyAmount')->default(0);
            $table->boolean('dailyAmountCheck')->default(false);

            $table->integer('priority')->nullable();
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
