<?php

use BuscaAtivaEscolar\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEducacensoIdToChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("children", function (Blueprint $table) {
        	$table->string('educacenso_id')->index()->nullable();
        });

        $user = User::create([
        	'tenant_id' => 'global',
        	'name' => 'Importação Educacenso INEP',
        	'email' => 'educacenso@buscaativaescolar.org.br',
	        'password' => str_random(64),
	        'type' => 'superuser',
	        'institution' => 'INEP',
			'position' => 'Ferramenta de importação',
        ]);

        $user->id = User::ID_EDUCACENSO_BOT;
        $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
