<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmailJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("email_jobs", function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->index();
            $table->string('status')->default('pending');
            $table->string('user_id');
            $table->string('tenant_id');
            $table->integer('school_id');
            $table->json('errors')->nullable();
            $table->string('school_email');
            $table->string('email_user');

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
        Schema::dropIfExists('email_jobs');
    }
}
