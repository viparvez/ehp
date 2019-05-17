<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreconditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preconditions', function (Blueprint $table) {
            $table->increments('id');
            $table->String('code');
            $table->String('name');
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
        Schema::dropIfExists('preconditions');
    }
}
