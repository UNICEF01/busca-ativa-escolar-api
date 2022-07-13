<?php

use BuscaAtivaEscolar\User;
use Illuminate\Database\Migrations\Migration;

class CreateUserBotToUploadXls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = User::create([
            'tenant_id' => 'global',
            'name' => 'Importação da escola ou município',
            'email' => 'importacao_xls@buscaativaescolar.org.br',
            'password' => str_random(64),
            'type' => 'superuser',
            'institution' => 'MUNICIPIO',
            'position' => 'Ferramenta de importação de arquivos personalizados XLS',
        ]);

        $user->id = User::ID_IMPORT_XLS_BOT;
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
