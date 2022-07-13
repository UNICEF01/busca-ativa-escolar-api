<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSchoolIdFieldsToSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

	    Schema::table("case_steps_pesquisa", function (Blueprint $table) {
		    $table->uuid('school_last_id')->index()->nullable()->before('school_last_name');
	    });

	    Schema::table("case_steps_rematricula", function (Blueprint $table) {
		    $table->uuid('school_id')->index()->nullable()->before('school_name');
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
