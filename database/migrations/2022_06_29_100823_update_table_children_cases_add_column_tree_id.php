<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableChildrenCasesAddColumnTreeId extends Migration
{
    /**
     * Run the migrations.
     *
     * Acréscimo da coluna tree_id na tabela children_cases
     *
     * @return void
     */
    public function up()
    {
        Schema::table('children_cases', function($table) {
            $table->string('tree_id', 150)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('children_cases', function($table) {
            $table->dropColumn('tree_id');
        });
    }
}
