<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('email')->unique();
            $table->integer('attempt');
            $table->integer('attempt_history');
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('attempted_at_history')->nullable();
            $table->boolean('blocked')->index()->default(false);
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
        Schema::dropIfExists('attempts');
    }
}
