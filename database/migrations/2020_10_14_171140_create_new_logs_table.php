<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_started')->nullable();
            $table->timestamp('date_completed')->nullable();
            $table->string('upload_link')->nullable();
            $table->string('export_link')->nullable();
            $table->string('action')->nullable();
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
        Schema::dropIfExists('new_logs');
    }
}
