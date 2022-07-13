<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_metrics_consolidated', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('tenant_id', 36)->index();
            $table->date('date')->index();
            $table->char('region', 15)->index();
            $table->char('state', 2)->index();
            $table->string('city')->index();

            $table->integer('in_observation')->index();
            $table->integer('out_of_school')->index();
            $table->integer('cancelled')->index();
            $table->integer('in_school')->index();
            $table->integer('interrupted')->index();
            $table->integer('transferred')->index();

            $table->integer('justified_cancelled')->index();
            $table->boolean('selo')->index()->default(false);
            $table->json('data')->nullable();
        });

//        Schema::create('daily_metrics', function (Blueprint $table) {
//            $table->char('tenant_id', 36);
//            $table->json('data');
//        });

//        Schema::create('daily_metrics', function (Blueprint $table) {
//            $table->date('date');
//            $table->json('data');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('daily_metrics_consolidated');
    }
}
