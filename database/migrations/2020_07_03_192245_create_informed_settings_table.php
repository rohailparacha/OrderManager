<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInformedSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('informed_settings', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('account_id');            
            $table->double('minAmount',8,2);
            $table->double('maxAmount',8,2);
            $table->string('strategy_id');
            $table->foreign('account_id')->references('id')->on('informed_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('informed_settings');
    }
}
