<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingTenantIdFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = ['children_cases', 'case_steps_alerta', 'case_steps_pesquisa', 'case_steps_analise_tecnica', 'case_steps_gestao_do_caso', 'case_steps_observacao', 'case_steps_rematricula'];

        foreach($tables as $table) {
        	Schema::table($table, function (Blueprint $table) {
        		$table->uuid('tenant_id')->index()->after('id')->nullable();
	        });
        }
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
