<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCiclo2FieldsToGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->integer('goal_ciclo2')->nullable();
            $table->integer('accumulated_ciclo2')->nullable();
            $table->date('final_ciclo2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn('goal_ciclo2');
            $table->dropColumn('accumulated_ciclo2');
            $table->dropColumn('final_ciclo2');
        });
    }
}