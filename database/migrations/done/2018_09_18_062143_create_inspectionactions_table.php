<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspectionactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inspection_id')->unsigned();
            $table->foreign('inspection_id')->references('id')->on('inspections');
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
        Schema::dropIfExists('inspectionactions');
    }
}
