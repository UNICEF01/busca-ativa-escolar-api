<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateChildrenCasesTableAddColumnGroupId extends Migration
{
    /**
     * Run the migrations.
     *
     * Acréscimo da coluna group_id (group_id)
     *
     * @return void
     */
    public function up()
    {
        Schema::table('children_cases', function($table) {
            $table->uuid('group_id')->index()->nullable();
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
            $table->dropColumn('group_id');
        });
    }
}
