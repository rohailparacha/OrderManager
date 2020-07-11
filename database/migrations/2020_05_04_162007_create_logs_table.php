<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_started')->nullable();
            $table->timestamp('date_completed')->nullable();
            $table->integer('identifiers')->nullable()->default(0);
            $table->integer('errorItems')->nullable()->default(0);
            $table->integer('successItems')->nullable()->default(0);        
            $table->string('error')->nullable();            
            $table->enum('stage',['SyncCentric','Informed','SellerActive'])->nullable();
            $table->enum('status',['Failed','Completed','In Progress']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
