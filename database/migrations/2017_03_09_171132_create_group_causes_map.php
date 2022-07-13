<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupCausesMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("group_causes", function (Blueprint $table) {
        	$table->increments('id');
        	$table->uuid('tenant_id')->index();
        	$table->uuid('group_id')->index();
        	$table->uuid('alert_cause_id')->index();
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
        //
    }
}
