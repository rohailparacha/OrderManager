<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('store');
            $table->unsignedBigInteger('scaccount_id');
            $table->unsignedBigInteger('infaccount_id');
            $table->string('username');
            $table->text('password');
            $table->string('manager_id');
            $table->integer('lagTime')->default(0);            
            $table->foreign('scaccount_id')->references('id')->on('sc_accounts');
            $table->foreign('infaccount_id')->references('id')->on('informed_accounts');
            $table->integer('informed_id');
            $table->integer('maxListingBuffer')->default(2);
            $table->integer('quantity')->default(100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
