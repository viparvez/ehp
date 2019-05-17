<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->String('code');
            $table->String('fname');
            $table->String('lname');
            $table->String('ssn');
            $table->String('medicaid');
            $table->date('dob');
            $table->String('email');
            $table->String('phone');
            $table->text('comment');
            $table->text('img_url');
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
        Schema::dropIfExists('clients');
    }
}
