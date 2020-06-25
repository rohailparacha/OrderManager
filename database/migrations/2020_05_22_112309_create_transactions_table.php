<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->date('date'); 
            $table->text('description')->nullable();
            $table->float('debitAmount')->default(0);
            $table->float('creditAmount')->default(0);
            $table->foreign('bank_id')->references('id')->on('bank_accounts');
            $table->foreign('category_id')->references('id')->on('accounting_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
