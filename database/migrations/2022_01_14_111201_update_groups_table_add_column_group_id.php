<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGroupsTableAddColumnGroupId extends Migration
{
    /**
     * Run the migrations.
     *
     * Acréscimo da coluna parent_id (group_id)
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function($table) {
            $table->uuid('parent_id')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function($table) {
            $table->dropColumn('parent_id');
        });
    }
}
