<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElectedMayourTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elected_mayour', function (Blueprint $table) {
            $table->increments('id', true);
            $table->string('nome', 200);
            $table->string('email', 100);
            $table->string('cpf', 11);
            $table->string('nm_titulo', 12);
            $table->string('uf', 2);
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
        Schema::dropIfExists('elected_mayour');
    }
}
