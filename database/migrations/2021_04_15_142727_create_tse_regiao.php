<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTseRegiao extends Migration
{
    public function up()
    {
        Schema::connection('trajetorias')->create('tse_regiao', function (Blueprint $table) {

            $table->integer('id',11);
            $table->string('nome',100);

        });
    }

    public function down()
    {
        Schema::dropIfExists('tse_regiao');
    }
}
