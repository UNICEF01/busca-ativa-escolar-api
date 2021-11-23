<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableGoalsFinalCiclo1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goals', function($table) {
            $table->integer('goal_ciclo1')->nullable();
            $table->integer('accumulated_ciclo1')->nullable();
            $table->date('final_ciclo1')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goals', function($table) {
            $table->dropColumn('goal_ciclo1');
            $table->dropColumn('accumulated_ciclo1');
            $table->dropColumn('final_ciclo1');
        });
    }
}
