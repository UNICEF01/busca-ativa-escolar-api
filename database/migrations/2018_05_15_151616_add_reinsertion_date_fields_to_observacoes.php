<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReinsertionDateFieldsToObservacoes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("case_steps_observacao", function (Blueprint $table) {
        	$table->date('reinsertion_date')->nullable();
        	$table->date('reinsertion_date_original')->nullable();
        	$table->string('reinsertion_date_change_reason')->nullable();
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
