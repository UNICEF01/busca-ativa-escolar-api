<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLgpdMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lgpd_mail', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('plataform_id');
            $table->string('mail');
            $table->dateTime('send_date');
            $table->dateTime('delivery_date')->nullable();
            $table->dateTime('open_date')->nullable();
            $table->dateTime('click_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lgpd_mail');
    }
}
