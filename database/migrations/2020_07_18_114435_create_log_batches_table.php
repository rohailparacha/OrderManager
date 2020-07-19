<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_batches', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('log_id');
            $table->string('name')->nullable();
            $table->timestamp('date_started')->nullable();
            $table->timestamp('date_completed')->nullable();
            $table->integer('totalItems')->nullable()->default(0);
            $table->integer('errorItems')->nullable()->default(0);
            $table->integer('successItems')->nullable()->default(0);        
            $table->string('error')->nullable();            
            $table->enum('stage',['Synccentric','Informed','SellerActive'])->nullable();
            $table->enum('status',['Failed','Completed','In Progress']);
            $table->foreign('log_id')->references('id')->on('logs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_batches');
    }
}
