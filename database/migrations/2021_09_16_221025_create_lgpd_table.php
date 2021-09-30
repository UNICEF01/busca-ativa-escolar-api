<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLgpdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lgpd', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            //Id do estado, cidade, usuário...que aceitou a lgpd.
            $table->uuid('plataform_id')->index()->unique();
            $table->string('name');

            $table->ipAddress('ip_addr')->nullable();
            $table->string('term_version');
            $table->timestamp('assigned_date');

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
        Schema::dropIfExists('lgpd');
    }
}
