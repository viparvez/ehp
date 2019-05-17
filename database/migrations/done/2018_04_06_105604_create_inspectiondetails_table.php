<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectiondetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspectiondetails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inspection_id')->unsigned();
            $table->foreign('inspection_id')->references('id')->on('inspections');
            $table->integer('apartment_id')->unsigned()->nullable();
            $table->foreign('apartment_id')->references('id')->on('apartments');
            $table->double('weightage',5,2);
            $table->text('comments');
            $table->enum('active', ['0','1'])->default('1');
            $table->enum('deleted', ['0','1'])->default('0');
            $table->actionByUser();
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
        Schema::dropIfExists('inspectiondetails');
    }
}
