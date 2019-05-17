<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id')->unsigned();
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->String('code');
            $table->String('name');
            $table->boolean('hasMedicine')->default(true);
            $table->boolean('hasHandicapAccess')->default(true);
            $table->boolean('isSmokeFree')->default(true);
            $table->boolean('hasElevator')->default(true);
            $table->text('address');
            $table->text('comment');
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
        Schema::dropIfExists('facilities');
    }
}
