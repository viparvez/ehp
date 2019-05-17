<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreconditionactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preconditionactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('precondition_id')->unsigned();
            $table->foreign('precondition_id')->references('id')->on('preconditions');
            $table->enum('deleted', ['0','1'])->default('0');
           $table->integer('createdbyuser_id')->unsigned();
            $table->foreign('createdbyuser_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preconditiontactions');
    }
}
