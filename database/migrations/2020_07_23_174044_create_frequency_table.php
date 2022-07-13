<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFrequencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frequency', function (Blueprint $table) {
            $table->increments('id', true);
            $table->string('qty_presence', 10);
            $table->string('qty_enrollment', 10);
            $table->integer('classes_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('frequency', function($table) {
            $table->foreign('classes_id')->references('id')->on('classes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frequency');
    }
}
