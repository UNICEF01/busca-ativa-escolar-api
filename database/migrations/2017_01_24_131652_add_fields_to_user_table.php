<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {

        	$table->date('dob')->nullable();
        	$table->string('cpf')->nullable();

        	$table->string('work_phone')->nullable();
			$table->string('work_mobile')->nullable();

			$table->string('personal_email')->nullable();
			$table->string('personal_mobile')->nullable();
			$table->string('skype_username')->nullable();

			$table->string('work_address')->nullable();
			$table->string('work_cep')->nullable();
			$table->string('work_neighborhood')->nullable();
			$table->string('work_city')->nullable();
			$table->string('work_uf')->nullable();

			$table->string('institution')->nullable();
			$table->string('position')->nullable();

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
