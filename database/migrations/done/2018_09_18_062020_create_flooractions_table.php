<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlooractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flooractions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('floor_id')->unsigned();
            $table->foreign('floor_id')->references('id')->on('floors');
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
        Schema::dropIfExists('flooractions');
    }
}
