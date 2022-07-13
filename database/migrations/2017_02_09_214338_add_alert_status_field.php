<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAlertStatusField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("children", function (Blueprint $table) {
        	$table->string('alert_status')->index()->default('pending');
        	$table->string('deadline_status')->index()->default('normal');
        });

        Schema::table("case_steps_alerta" , function (Blueprint $table) {
        	$table->string('alert_status')->index()->default('pending');
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
