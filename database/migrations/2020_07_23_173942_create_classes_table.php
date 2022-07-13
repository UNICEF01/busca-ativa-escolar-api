<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->increments('id', true);
            $table->string('name', 100);
            $table->string('shift', 50); //período Matutino, Vespertino ou Noturno
            $table->string('qty_enrollment', 10);
            $table->timestamps();
            $table->softDeletes();
            $table->string('schools_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classes');
    }
}
