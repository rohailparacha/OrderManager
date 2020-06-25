<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGmailAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gmail_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('url')->nullable();            
            $table->string('bceurl')->nullable();      
            $table->enum('accountType', ['Regular','Business']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gmail_accounts');
    }
}
