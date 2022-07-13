<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePnad extends Migration
{
    protected $connection = 'trajetorias';

    public function up()
    {
        Schema::connection('trajetorias')->create('pnad', function (Blueprint $table) {

            $table->bigIncrements('id')->autoIncrement();
            $table->integer('id_regiao');
            $table->bigInteger('id_uf',);
            $table->bigInteger('id_municipio');
            $table->integer('id_localizacao');
            $table->integer('id_faixa_etaria');
            $table->double('value_masc', 32, 16);
            $table->double('value_femn', 32, 16);
            $table->double('value_ba', 32, 16); //branco e amarelos
            $table->double('value_pni', 32, 16); //pardos, negros e indígenas
            $table->double('value_sim', 32, 16); //frequência
            $table->double('value_nao', 32, 16); //frequência.
            $table->double('value_pb', 32, 16); //20% mais pobres
            $table->double('value_int', 32, 16); //60% intermediário
            $table->double('value_rc', 32, 16); //20% mais tico
            $table->double('total', 32, 16); //valor total
            $table->foreign('id_regiao')->references('id')->on('tse_regiao');
            $table->foreign('id_uf')->references('id')->on('te_estados');
            $table->foreign('id_municipio')->references('id')->on('te_municipios');
            $table->foreign('id_localizacao')->references('id')->on('tse_localizacao');
            $table->foreign('id_faixa_etaria')->references('id')->on('tse_faixa_etaria');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pnad');
    }
}
